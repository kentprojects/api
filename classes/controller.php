<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Controller
{
	/**
	 * @var Auth
	 */
	protected $auth;
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;
	/**
	 * @var Request_Internal
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @throws HttpStatusException
	 */
	public function __construct(Request_Internal &$request, Response &$response)
	{
		$this->request = $request;
		$this->response = $response;

		$this->auth = new Auth($request, $response, $this->authentication);
	}

	/**
	 * To be run BEFORE the main action.
	 */
	public function before()
	{

	}

	/**
	 * To be run AFTER the main action.
	 */
	public function after()
	{
		$this->response->header("Content-Type", "application/json");
		$this->response->body(
			json_encode(
				$this->response->body(),
				JSON_PRETTY_PRINT
			)
		);
	}
}