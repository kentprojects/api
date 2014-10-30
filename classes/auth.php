<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Auth
{
	const NONE = "auth:none";
	const APP  = "auth:app";
	const USER = "auth:user";

	protected $level;
	protected $request;
	protected $response;

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param String $level
	 * @throws HTTPStatusException
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
				throw new HTTPStatusException(400, "Missing application key.");
			}

			if ($this->request->query("expires", null) === null)
			{
				throw new HTTPStatusException(400, "Missing expiry timestamp");
			}

			if ($this->request->query("signature", null) === null)
			{
				throw new HTTPStatusException(400, "Missing signature");
			}

			if (intval($this->request->query("expires")) < time())
			{
				throw new HTTPStatusException(400, "Expired request");
			}

			$this->application = Model_Application::getByKey($this->request->query("key"));
			if (empty($this->application))
			{
				throw new HTTPStatusException(400, "Invalid application.");
			}

			$query = $this->request->getQueryData();
			unset($query["signature"]);

			// Ensure all keys and values are sorted & strings.
			ksort($query);
			array_walk($query, function(&$v) { $v = (string) $v; });

			$local = md5(config("checksum", "salt") . $this->application->getSecret() . json_encode($query));

			if ($local !== $this->request->query("signature"))
			{
				throw new HTTPStatusException(400, "Invalid signature.");
			}
		}
	}
}