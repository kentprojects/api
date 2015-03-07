<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Intent extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::USER;

	/**
	 * /intent
	 * /intent/:id
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::PUT);

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /intent
			 */

			if ($this->request->param("id") !== null)
			{
				throw new HttpStatusException(400, "You cannot create an intent using an existing intent ID.");
			}

			$params = $this->validateParams(array(
				"handler" => $this->request->post("handler", false),
				"data" => $this->request->post("data", array())
			));

			/**
			 * @var Intent $class
			 * @var Intent $intent
			 */
			$class = Intent::getHandlerClassName($params["handler"]);
			$intent = new $class(new Model_Intent(
				$this->auth->getUser(), Intent::formatHandler($params["handler"]), Intent::STATE_OPEN
			));
			if (!$intent->canCreate($this->auth->getUser()))
			{
				throw new HttpStatusException(403, "You do not have permission to create this intent.");
			}
			$intent->create($params["data"], $this->auth->getUser());

			$this->response->status(201);
			$this->response->body($intent);

			return;
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "Missing intent ID.");
		}
		$intent = Intent::getById($this->request->param("id"));
		/** @var Intent $intent */
		if (empty($intent))
		{
			throw new HttpStatusException(404, "Intent not found.");
		}

		if (!$intent->canRead($this->auth->getUser()))
		{
			throw new HttpStatusException(403, "You do not have permission to read this intent.");
		}

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /intent/:id
			 */

			if ($intent->state() !== Intent::STATE_OPEN)
			{
				throw new HttpStatusException(500, "You can't update an intent that isn't OPEN.");
			}

			if (!$intent->canUpdate($this->auth->getUser()))
			{
				throw new HttpStatusException(403, "You do not have permission to update this intent.");
			}

			if ($this->request->post("state", null) !== null)
			{
				$intent->state("intent:state:" . strtolower($this->request->post("state")));
			}
			$intent->update($this->request->post("data", array()), $this->auth->getUser());
		}

		Log::debug($intent);

		/**
		 * GET /intent/:id
		 */
		$this->response->status(200);
		$this->response->header("KP-Intent-Hash", $intent->getHash());
		$this->response->body($intent);
	}
}