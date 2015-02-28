<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class KentProjects_Controller_TestBase extends KentProjects_TestBase
{
	protected $applicationKey = "ad7921ce757a74d8676c9140ec498003";
	protected $applicationSecret = "be0855399d72ad351807f3eeecec5ade";

	protected $userTokenConvener = "daa4ed4e5994c355197cc17bb52bf0d9";
	protected $userTokenSupervisor = "e529609067c6dd7fcb1e744f3f634adf";
	protected $userTokenStudent = "3865caf68614ce90f15c5f77cdbbb8b9";

	/**
	 * @param string $method
	 * @param array $data
	 * @return Request_Internal
	 */
	protected function createBadSignedRequest($method, array $data = array())
	{
		$request = Request::factory($method, "/test");
		$getData = $this->signRequest(!empty($data["get"]) ? $data["get"] : array());
		$getData[uniqid()] = uniqid();

		$request->setQueryData($getData);
		$request->setPostData(!empty($data["post"]) ? $data["post"] : array());
		$request->setParamData(!empty($data["param"]) ? $data["param"] : array());
		return $request;
	}

	/**
	 * @param string $method
	 * @param array $data
	 * @param string $user
	 * @return Request_Internal
	 */
	protected function createSignedRequest($method, array $data = array(), $user = null)
	{
		$request = Request::factory($method, "/test");
		$request->setQueryData($this->signRequest(!empty($data["get"]) ? $data["get"] : array(), $user));
		$request->setPostData(!empty($data["post"]) ? $data["post"] : array());
		$request->setParamData(!empty($data["param"]) ? $data["param"] : array());
		return $request;
	}

	/**
	 * @param string $method
	 * @param array $data
	 * @return Request_Internal
	 */
	protected function createUnsignedRequest($method, array $data = array())
	{
		$request = Request::factory($method, "/test");
		$request->setQueryData(!empty($data["get"]) ? $data["get"] : array());
		$request->setPostData(!empty($data["post"]) ? $data["post"] : array());
		$request->setParamData(!empty($data["param"]) ? $data["param"] : array());
		return $request;
	}

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param string $controller
	 * @param string $action
	 * @return void
	 */
	protected function runController(Request_Internal &$request, Response &$response, $controller, $action = "index")
	{
		$controller = "Controller_" . $controller;
		$action = "action_" . $action;

		/** @var Controller $controller */
		$controller = new $controller($request, $response);
		$controller->before();
		$controller->$action();
		$controller->after();
	}

	/**
	 * @param array $getData
	 * @param string $token
	 * @return array
	 */
	private function signRequest(array $getData, $token = null)
	{
		$forcedGetData = array(
			"key" => $this->applicationKey,
			"expires" => time() + 600
		);
		if (!empty($token))
		{
			$userToken = "UserToken" . ucfirst($token);
			if (empty($this->$userToken))
			{
				throw new InvalidArgumentException("Invalid token '$token'.");
			}
			$forcedGetData["user"] = $this->$userToken;
		}

		$getData = array_merge($forcedGetData, $getData);

		unset($getData["signature"]);
		ksort($getData);
		array_walk(
			$getData,
			function (&$v)
			{
				$v = (string)$v;
			}
		);
		$getData["signature"] = md5(
			config("checksum", "salt") . $this->applicationSecret . json_encode($getData)
		);

		return $getData;
	}
}