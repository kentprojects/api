<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class_exists("Request");
class Admin_Request extends Request_Internal
{
	public function __construct($method, $url)
	{
		parent::__construct($method, $url);
	}

	/**
	 * Run the internal request!
	 * @throws RequestException
	 * @return Response
	 */
	public function execute()
	{
		if ($this->run === true)
		{
			/**
			 * This prevents idiots running something akin to: $this->request->execute();
			 */
			throw new RequestException(sprintf(
				"You cannot execute this internal request within it's own execute process: %s:%s ",
				strtoupper($_SERVER["REQUEST_METHOD"]), $_SERVER["PATH_INFO"]
			));
		}
		$this->run = true;

		Timing::start("request");
		$response = new Admin_Response($this);
		try
		{
			$this->param = Admin_Router::handle($this->getUrl());

			// Load the relevant Controller
			$controller = "Admin_Controller_" . ucfirst($this->param("controller"));
			if (!class_exists($controller))
			{
				throw new RequestException("$controller was not found.");
			}
			if (!is_subclass_of($controller, "Admin_Controller"))
			{
				throw new RequestException("$controller does not extend Admin_Controller.");
			}

			// Check the action is valid.
			$action = "action_" . $this->param("action");
			if (!method_exists($controller, $action))
			{
				throw new RequestException("Method $controller::$action was not found.");
			}

			//Log::debug($this, $controller, $action);

			// Run the Controller and the relevant Action
			/** @var Admin_Controller $controller */
			$controller = new $controller($this, $response);
			$controller->before();
			$controller->$action();
			$controller->after();
		}
		catch (HTTPRedirectException $e)
		{
			/**
			 * Handle the redirect, by creating a new Response object.
			 */
			$response = new Admin_Response($this);
			$response->status($e->getCode());
			$response->header("Location", $e->getLocation());
		}
		catch (Exception $e)
		{
			$response = new Admin_Response($this);
			$response->headers(array(
				"Exception" => get_class($e)
			));

			if ($e instanceof HTTPRedirectException)
			{
				$response->header("Location", $e->getLocation());
			}

			if (!class_exists("Controller_Error"))
			{
				throw new RequestException("Missing error controller.", 0, $e);
			}

			/** @var Admin_Controller_Error $controller */
			$controller = new Admin_Controller_Error($this, $response);
			$controller->action($e);

			Log::error($e);
		}
		Timing::stop("request");
		return $response;
	}
}