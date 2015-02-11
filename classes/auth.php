<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Auth
{
	const NONE = "auth:none";
	const APP = "auth:app";
	const USER = "auth:user";

	/**
	 * @var Model_Application
	 */
	protected $application;
	/**
	 * @var String
	 */
	protected $level;
	/**
	 * @var Request_Internal
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;
	/**
	 * @var Model_Token
	 */
	protected $token;

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param String $level
	 * @throws HttpStatusException
	 */
	public function __construct(Request_Internal &$request, Response &$response, $level)
	{
		$this->level = $level;
		$this->request = $request;
		$this->response = $response;

		if ($this->level !== self::NONE)
		{
			if ($this->request->query("key", null) === null)
			{
				throw new HttpStatusException(400, "Missing application key.");
			}

			if ($this->request->query("expires", null) === null)
			{
				throw new HttpStatusException(400, "Missing expiry timestamp.");
			}

			if ($this->request->query("signature", null) === null)
			{
				throw new HttpStatusException(400, "Missing signature.");
			}

			if (intval($this->request->query("expires")) < time())
			{
				throw new HttpStatusException(400, "Expired request.");
			}

			if (($this->level === self::USER) && ($this->request->query("user", null) === null))
			{
				throw new HttpStatusException(400, "Missing user token.");
			}

			$this->application = Model_Application::getByKey($this->request->query("key"));
			if (empty($this->application))
			{
				throw new HttpStatusException(400, "Invalid application.");
			}

			$query = $this->request->getQueryData();
			{
				unset($query["signature"]);
				ksort($query);
				array_walk(
					$query,
					function (&$v)
					{
						$v = (string)$v;
					}
				);
			}
			$local = md5(config("checksum", "salt") . $this->application->getSecret() . json_encode($query));
			if ($local !== $this->request->query("signature"))
			{
				if (false)
				{
					error_log(json_encode(array(
						"INVALID" => "SIGNATURE",
						"local" => $local,
						"remote" => $this->request->query("signature"),
						"get" => $this->request->getQueryData(),
						"app" => $this->application,
						"sum" => config("checksum", "salt") . $this->application->getSecret() . json_encode($query)
					)));
				}
				throw new HttpStatusException(400, "Invalid signature.");
			}

			$this->token = Model_Token::getByToken($this->request->query("user", null));
			/**
			 * If we require user authentication, and we don't have a user, then throw an exception!
			 */
			if (($this->level === self::USER) && empty($this->token))
			{
				throw new HttpStatusException(400, "Invalid user.");
			}
		}
		else
		{
			if ($this->request->query("key", null) !== null)
			{
				$this->application = Model_Application::getByKey($this->request->query("key"));
				if (empty($this->application))
				{
					throw new HttpStatusException(400, "Invalid application.");
				}
				$this->token = Model_Token::getByToken($this->request->query("user", null));
			}
		}
	}

	/**
	 * @return Model_Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * @return Model_User
	 */
	public function getUser()
	{
		return empty($this->token) ? null : $this->token->getUser();
	}

	/**
	 * @return bool
	 */
	public function hasUser()
	{
		$user = $this->getUser();
		return !empty($user) && ($user instanceof Model_User);
	}
}