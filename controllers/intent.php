<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Intent extends Controller
{
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

			/** @var Intent $class */
			$class = Intent::getHandlerClassName($params["handler"]);
			/** @var Intent $intent */
			$intent = new $class(new Model_Intent($this->auth->getUser(), Intent::formatHandler($params["handler"])));
			$intent->create($params["data"]);

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

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /intent/:id
			 */
			throw new HttpStatusException(501, "Updating an intent is coming soon.");
			/** @noinspection PhpUnreachableStatementInspection */
			$intent->update($this->request->post("data", array()));
		}

		/**
		 * GET /intent/:id
		 */
		$this->response->status(200);
		$this->response->body($intent);
	}
}