<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Auth extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;

	/**
	 * GET /auth/confirm
	 *
	 * @throws HttpStatusException
	 */
	public function action_confirm()
	{
		$this->validateMethods(Request::POST);

		if ($this->request->post("code") === null)
		{
			throw new HttpStatusException(400, "Missing state code.");
		}

		$user = $this->validateCode($this->request->post("code"));
		if (empty($user))
		{
			throw new HttpStatusException(400, "Invalid authentication token.");
		}
		$this->clearCode($this->request->post("code"));

		$output = array(
			"token" => $this->createApiToken($user),
			"user" => $user
		);
		$this->response->status(200);
		$this->response->body(json_encode($output));
	}

	/**
	 * GET /auth
	 */
	public function action_index()
	{
		$this->action_internal();
	}

	/**
	 * GET /auth/internal
	 *
	 * @throws HttpStatusException
	 */
	public function action_internal()
	{
		$this->validateMethods(Request::GET);

		if ($this->request->query("auth") === null)
		{
			throw new HttpStatusException(400, "Missing state code.");
		}

		$fakeCodes = array(
			"f4dfeada0e91e1791a80da1bb26a7d96" => array(
				"role" => "staff",
				"username" => "J.C.Hernandez-Castro"
			),
			"1e9a755d73865da9068f079d81402ce7" => array(
				"role" => "staff",
				"username" => "J.S.Crawford"
			),
			"6f2653c2a1c64220e3d2a713cc52b438" => array(
				"role" => "staff",
				"username" => "supervisor2"
			),
			"1f18ed87771daf095e090916cb9423e4" => array(
				"role" => "student",
				"username" => "mh471"
			),
			"1460357d62390ab9b3b33fa1a0618a8f" => array(
				"role" => "student",
				"username" => "jsd24"
			),
			"930144ea545ce754789b15074106bc36" => array(
				"role" => "student",
				"username" => "mjw59"
			),
		);
		/** @noinspection SpellCheckingInspection */
		$url = parse_url($_SERVER["HTTP_REFERER"]);

		if (!array_key_exists($this->request->query("auth"), $fakeCodes))
		{
			throw new HttpStatusException(400, "Invalid state code.");
		}

		$authUser = $fakeCodes[$this->request->query("auth")];

		// print_r($url); print_r($authUser); exit(1);

		$user = Model_User::getByEmail($authUser["username"] . "@kent.ac.uk");
		if (empty($user))
		{
			$user = new Model_User;
			$user->setEmail($authUser["username"] . "@kent.ac.uk");
			$user->setRole($authUser["role"]);
			$user->save();
		}

		throw $this->generateAuthUrl($url, $user);
	}

	/**
	 * GET /auth/sso
	 *
	 * @throws HttpStatusException
	 * @throws HttpRedirectException
	 */
	public function action_sso()
	{
		$this->validateMethods(Request::GET);

		session_name("KentProjectsAuthentication");
		session_start();

		if (!empty($_SERVER["HTTP_REFERER"]) && empty($_SESSION["incoming-url"]))
		{
			$_SESSION["incoming-url"] = $_SERVER["HTTP_REFERER"];
		}

		if (config("environment") === "development")
		{
			if ($this->request->query("data") === null)
			{
				throw new HttpRedirectException(302, "http://api.kentprojects.com/auth/sso?return=dev");
			}
			else
			{
				$attributes = $this->getCacheData($this->request->query("data"));
				if (empty($attributes))
				{
					throw new HttpStatusException(400, "Empty data back from live SSO.");
				}
			}
		}
		else
		{
			/** @noinspection PhpUndefinedClassInspection */
			$provider = new SimpleSAML_Auth_Simple("default-sp");
			/** @noinspection PhpUndefinedMethodInspection */
			$provider->requireAuth();
			/** @var array $attributes */
			/** @noinspection PhpUndefinedMethodInspection */
			$attributes = $provider->getAttributes();

			if (true)
			{
				print_r($attributes);
				exit(1);
			}

			if ($this->request->query("return") === "dev")
			{
				$key = md5(uniqid());
				$this->setCacheData($key, $attributes);
				throw new HttpRedirectException(302, "http://api.dev.kentprojects.com/auth/sso?data=" . $key);
			}
		}

		$user = Model_User::getByEmail($attributes["username"] . $this->$emailDomain);
		if (empty($user))
		{
			$user = new Model_User;
			$user->setEmail($attributes["username"] . $this->$emailDomain);
			$user->setRole($attributes["role"]);
			$user->save();
		}

		$url = parse_url(!empty($_SESSION["incoming-url"]) ? $_SESSION["incoming-url"] : $this->$backupUrl);

		session_destroy();

		throw $this->generateAuthUrl($url, $user);
	}

	/**
	 * @param string $code
	 * @return void
	 */
	private function clearCode($code)
	{
		Database::prepare("DELETE FROM `Authentication` WHERE `token` = ?", "s")->execute($code);
	}

	/**
	 * @param Model_User $user
	 * @throws DatabaseException
	 * @return string
	 */
	protected function createApiToken(Model_User $user)
	{
		$break = false;
		$token = null;
		$statement = Database::prepare("INSERT INTO `Token` (`user_id`, `token`) VALUES (?,?)", "is");
		while (!$break)
		{
			$token = md5(uniqid());
			try
			{
				$statement->execute($user->getId(), $token);
				$break = true;
			}
			catch (DatabaseException $e)
			{
				// Work out if this is a duplicate key issue. If so, let it loop again. Else, throw the exception.
				if (true)
				{
					throw $e;
				}
			}
		}
		return $token;
	}

	/**
	 * @param array $url
	 * @param Model_User $user
	 * @throws DatabaseException
	 * @return HttpRedirectException
	 */
	protected function generateAuthUrl($url, Model_User $user)
	{
		$break = false;
		$token = null;
		$statement = Database::prepare("INSERT INTO `Authentication` (`user_id`, `token`) VALUES (?,?)", "is");
		while (!$break)
		{
			$token = md5(uniqid());
			try
			{
				$statement->execute($user->getId(), $token);
				$break = true;
			}
			catch (DatabaseException $e)
			{
				// Work out if this is a duplicate key issue. If so, let it loop again. Else, throw the exception.
				if (true)
				{
					throw $e;
				}
			}
		}

		return new HttpRedirectException(
			302, $url["scheme"] . "://" . $url["host"] . (!empty($url["port"]) ? ":" . $url["port"] : "") .
			"/login.php?success=" . $token
		);
	}

	/**
	 * @param string $key
	 * @return array
	 */
	protected function getCacheData($key)
	{
		$key = "auth/sso-dev/" . $key;
		return null;
	}

	/**
	 * @param string $key
	 * @param array $data
	 * @return void
	 */
	protected function setCacheData($key, array $data)
	{
		$key = "auth/sso-dev/" . $key;
	}

	/**
	 * @param string $code
	 * @return Model_User
	 */
	private function validateCode($code)
	{
		$user_id = Database::prepare("SELECT `user_id` FROM `Authentication` WHERE `token` = ?", "s")
			->execute($code)->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}
}