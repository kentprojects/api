<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Controller
{
	/**
	 * @var ACL
	 */
	protected $acl;
	/**
	 * @var Auth
	 */
	protected $auth;
	/**
	 * @var string
	 */
	protected $authentication = Auth::USER;
	/**
	 * @var bool
	 */
	private $isHeadRequest = false;
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

		if ($this->request->getMethod() === Request::HEAD)
		{
			$this->isHeadRequest = true;
			$this->request->setMethod(Request::GET);
		}

		$this->auth = new Auth($request, $response, $this->authentication);
		$this->acl = new ACL($this->auth->getUser());
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

		$this->response->header("Content-Length", strlen($this->response->body()));

		if ($this->isHeadRequest)
		{
			$this->response->body("");
		}
	}

	/**
	 * Validates that the current method is allowed by this action.
	 * Nicely handles all the exception throwing.
	 *
	 * @param string $method
	 * [ @param string $method ] ...
	 * @throws HttpStatusException
	 */
	protected function validateMethods($method)
	{
		if (!in_array($this->request->getMethod(), func_get_args()))
		{
			throw new HttpStatusException(501);
		}
	}

	/**
	 * This is a brilliant little function to handle all the boring "does this parameter exist" kinda ting.
	 * Basically, if the parameter evaluates to "false", it's an error.
	 * But it could be "null".
	 *
	 * If you want a parameter to be genuinely "false", then handle it outside this method.
	 *
	 * @param array $data
	 * @throws HttpStatusException
	 * @return array
	 */
	protected function validateParams(array $data)
	{
		foreach ($data as $key => $value)
		{
			if ($value === false)
			{
				throw new HttpStatusException(400, "Missing parameter {$key} for this request.");
			}
		}
		return $data;
	}
}