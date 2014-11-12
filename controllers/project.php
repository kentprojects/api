<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Project extends Controller
{
	/**
	 * /project
	 * /project/:id
	 */
	public function action_index()
	{
		if (!in_array($this->request->getMethod(), array(Request::GET, Request::POST)))
		{
			throw new HttpStatusException(501);
		}

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * Used to create a new projects!
			 */
			$this->response->status(201);
			return;
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No project id provided.");
		}

		$project = Model_Project::getById($this->request->param("id"));
		if (empty($project))
		{
			throw new HttpStatusException(404, "Project not found.");
		}

		$this->response->status(200);
		$this->response->body($project);
	}
}