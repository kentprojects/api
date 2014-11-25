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
	 * @var array
	 */
	protected $applications = array();
	/**
	 * @var stdClass
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
	 * @var Model_User
	 */
	protected $user;

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param String $level
	 * @throws HttpStatusException
	 */
	public function __construct(Request_Internal &$request, Response &$response, $level)
	{
		$applications = parse_ini_file(APPLICATION_PATH . "/applications.ini", true);
		foreach ($applications as $name => $details)
		{
			if (empty($details["key"]) || empty($details["secret"]))
			{
				error_log("The application {$name} doesn't have a KEY & a SECRET.");
				continue;
			}
			$this->applications[$details["key"]] = (object)array(
				"name" => $name,
				"secret" => $details["secret"]
			);
		}

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

			if (empty($this->applications[$this->request->query("key")]))
			{
				throw new HttpStatusException(400, "Invalid application.");
			}

			if (($this->level === self::USER) && ($this->request->query("user", null) === null))
			{
				throw new HttpStatusException(400, "Missing user token.");
			}

			$this->application = $this->applications[$this->request->query("key")];

			$query = $this->request->getQueryData();
			unset($query["signature"]);

			// Ensure all keys and values are sorted & strings.
			ksort($query);
			array_walk($query, function (&$v)
			{
				$v = (string)$v;
			});

			$local = md5(config("checksum", "salt") . $this->application->secret . json_encode($query));

			if ($local !== $this->request->query("signature"))
			{
				throw new HttpStatusException(400, "Invalid signature.");
			}

			if ($this->level === self::USER)
			{
				/**
				 * Authenticate the user.
				 * Using the token, find the user!
				 */
			}
		}
	}

	/**
	 * @return stdClass
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
		return $this->user;
	}
}