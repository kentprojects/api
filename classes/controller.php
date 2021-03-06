<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Controller
 * This represents a basic controller that takes requests in and returns responses out.
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
	 * @return void
	 */
	public function before()
	{
		Timing::start("controller");
	}

	/**
	 * To be run AFTER the main action.
	 * @return void
	 */
	public function after()
	{
		$this->response->header("Content-Type", "application/json");
		$this->response->body(json_encode($this->render($this->response->body()), JSON_PRETTY_PRINT));

		$this->response->header("Content-Length", strlen($this->response->body()));

		if ($this->isHeadRequest)
		{
			$this->response->body("");
		}
		Timing::stop("controller");
	}

	/**
	 * This is our custom render function that goes through each result, ensuring it's in a format that JSON-encode
	 * will accept.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	private function render($body)
	{
		if (is_object($body))
		{
			if ($body instanceof Intent || $body instanceof Model || $body instanceof ModelMap ||
				$body instanceof UserYearMap
			)
			{
				return $body->render($this->request, $this->response, $this->acl);
			}
			else
			{
				return json_decode(json_encode($body));
			}
		}
		elseif (is_array($body))
		{
			foreach ($body as $i => $b)
			{
				$body[$i] = $this->render($b);
			}
			return $body;
		}
		else
		{
			return json_decode(json_encode($body));
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
				throw new HttpStatusException(400, "Missing parameter '{$key}' for this request.");
			}
		}
		return $data;
	}

	/**
	 * This is a clever function to handle all the user permissions.
	 * Uses an array to set various requirements for an action.
	 *
	 * @param array $requirements An array of requirements to check against.
	 * @throws HttpStatusException
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	protected function validateUser(array $requirements)
	{
		if (empty($requirements["entity"]) || empty($requirements["action"]))
		{
			throw new InvalidArgumentException("Missing 'entity' and/or 'action' key for requirements.");
		}

		if (empty($requirements["message"]))
		{
			$requirements["message"] = "You aren't allowed to do this action.";
		}

		if (!empty($requirements["role"]))
		{
			$user = $this->auth->getUser();
			$requirements["missing-user-message"] = "No user found to authenticate this action against.";

			if (empty($user))
			{
				throw new HttpStatusException(400, $requirements["missing-user-message"]);
			}
			if ($user->getRole() !== $requirements["role"])
			{
				throw new HttpStatusException(
					400, "You must be the role of '{$requirements["role"]}' to do this action."
				);
			}
		}

		if (!$this->acl->validate($requirements["entity"], $requirements["action"]))
		{
			throw new HttpStatusException(400, $requirements["message"]);
		}
	}
}