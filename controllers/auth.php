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
	 * @var string
	 */
	protected $prefixCacheKey = "auth.confirm.";

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
		$this->validateMethods(Request::GET, Request::POST);

		session_start();
		$backupUrl = "http://" . (config("environment") === "development" ? "dev." : "") . "kentprojects.com";
		$prefixDevCacheKey = Cache::PREFIX . "auth.dev.sso.";

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
				$attributes = Cache::getOnce($prefixDevCacheKey . $this->request->query("data"));
				if (empty($attributes))
				{
					throw new HttpStatusException(500, "Empty data back from live SSO.");
				}
			}
		}
		else
		{
			/**
			 * @require The external SimpleSAML2 library.
			 */
			/** @noinspection PhpIncludeInspection */
			/** @noinspection SpellCheckingInspection */
			require_once "/var/www/simplesaml/lib/_autoload.php";

			/** @noinspection PhpUndefinedClassInspection */
			$provider = new SimpleSAML_Auth_Simple("default-sp");
			/** @noinspection PhpUndefinedMethodInspection */
			$provider->requireAuth();
			/** @var array $attributes */
			/** @noinspection PhpUndefinedMethodInspection */
			$attributes = $provider->getAttributes();

			if (false)
			{
				header("Content-type: text/plain");
				print_r($attributes);
				exit(1);
			}

			if (empty($attributes))
			{
				throw new HttpStatusException(500, "Empty data back from SSO.");
			}

			if ($this->request->query("return") === "dev")
			{
				$key = md5(uniqid());
				if (Cache::set($prefixDevCacheKey . $key, $attributes, 10 * Cache::MINUTE) === false)
				{
					trigger_error(
						"Unable to save {$prefixDevCacheKey}{$key} => " . json_encode($attributes), E_USER_ERROR
					);
				}
				throw new HttpRedirectException(302, "http://api.dev.kentprojects.com/auth/sso?data=" . $key);
			}
		}

		if (empty($attributes))
		{
			throw new HttpStatusException(500, "Empty data back from the SSO.");
		}
		elseif (!is_array($attributes))
		{
			error_log("Invalid format returned from the SSO: " . json_encode($attributes));
			throw new HttpStatusException(500, "Invalid format returned from the SSO.");
		}
		/** @noinspection SpellCheckingInspection */
		elseif (empty($attributes["unikentaccountType"]) || empty($attributes["mail"]) || empty($attributes["uid"]))
		{
			error_log("Invalid format returned from the SSO: " . json_encode($attributes));
			throw new HttpStatusException(500, "Invalid format returned from the SSO.");
		}
		/** @noinspection SpellCheckingInspection */
		elseif (!is_array($attributes["unikentaccountType"]) || !is_array($attributes["mail"]) || !is_array($attributes["uid"]))
		{
			error_log("Invalid format returned from the SSO: " . json_encode($attributes));
			throw new HttpStatusException(500, "Invalid format returned from the SSO.");
		}

		if (true)
		{
			header("Content-type: text/plain");
			print_r($attributes);
			exit(1);
		}

		$email = current($attributes["mail"]);
		/** @noinspection SpellCheckingInspection */
		$role = current($attributes["unikentaccountType"]);
		$uid = current($attributes["uid"]);

		if (empty($email) || empty($role) || empty($uid))
		{
			error_log("Invalid data returned from the SSO: " .
				json_encode(array("email" => $email, "role" => $role, "uid" => $uid)));
			throw new HttpStatusException(500, "Invalid data returned from the SSO.");
		}

		$user = Model_User::getByEmail($email);
		if (empty($user))
		{
			$user = new Model_User;
			$user->setEmail($email);
			$user->setRole($role);
			$user->save();
		}

		$url = parse_url(!empty($_SESSION["incoming-url"]) ? $_SESSION["incoming-url"] : $backupUrl);

		session_destroy();

		throw $this->generateAuthUrl($url, $user);
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
	 * @return HttpRedirectException
	 */
	protected function generateAuthUrl(array $url, Model_User $user)
	{
		$break = false;
		$token = null;

		while (!$break)
		{
			$token = md5(uniqid());
			$break = Cache::add(Cache::PREFIX . $this->prefixCacheKey . $token, $user->getId());
		}

		$url = $url["scheme"] . "://" . $url["host"] . (!empty($url["port"]) ? ":" . $url["port"] : "") .
			"/login.php?success=" . $token;

		error_log("AuthURL is " . $url);

		return new HttpRedirectException(302, $url);
	}

	/**
	 * @param string $code
	 * @return Model_User
	 */
	private function validateCode($code)
	{
		$user_id = Cache::getOnce(Cache::PREFIX . $this->prefixCacheKey . $code, null);
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}
}