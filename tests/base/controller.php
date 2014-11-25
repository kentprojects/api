<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class KentProjects_Controller_TestBase extends KentProjects_TestBase
{
	/**
	 * @param string $method
	 * @param array $getData
	 * @param array $postData
	 * @param array $paramData
	 * @return Request_Internal
	 */
	protected function createBadRequest($method, array $getData = array(), array $postData = array(), array $paramData = array())
	{
		$request = Request::factory($method, "/test");
		$getData = $this->signRequest($getData);
		$getData[uniqid()] = uniqid();

		$request->setQueryData($getData);
		$request->setPostData($postData);
		$request->setParamData($paramData);
		return $request;
	}

	/**
	 * @param string $method
	 * @param array $getData
	 * @param array $postData
	 * @param array $paramData
	 * @return Request_Internal
	 */
	protected function createGoodRequest($method, array $getData = array(), array $postData = array(), array $paramData = array())
	{
		$request = Request::factory($method, "/test");
		$request->setQueryData($this->signRequest($getData));
		$request->setPostData($postData);
		$request->setParamData($paramData);
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
	 * @return array
	 */
	private function signRequest(array $getData)
	{
		$applications = parse_ini_file(APPLICATION_PATH . "/applications.ini", true);
		$getData = array_merge(
			array(
				"key" => $applications["phpunit"]["key"],
				"expires" => time() + 600,
			),
			$getData
		);
		unset($getData["signature"]);
		ksort($getData);
		array_walk($getData, function (&$v)
		{
			$v = (string)$v;
		});
		$getData["signature"] = md5(
			config("checksum", "salt") . $applications["phpunit"]["secret"] . json_encode($getData)
		);

		return $getData;
	}
}