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
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::PUT, Request::DELETE);

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /project
			 * Used to create a project.
			 */

			if ($this->request->param("id") !== null)
			{
				throw new HttpStatusException(400, "You cannot create a project using an existing project ID.");
			}

			/**
			 * Validate that the user can create a project.
			 */
			$this->validateUser(array(
				"entity" => "project",
				"action" => ACL::CREATE,
				"message" => "You do not have permission to create a project."
			));
			/**
			 * Validate parameters.
			 */
			$params = $this->validateParams(array(
				"name" => $this->request->post("name", false),
				"creator" => $this->auth->getUser()->getId()
			));

			$creator = Model_User::getById($params["creator"]);
			if (empty($creator))
			{
				throw new HttpStatusException(400, "Invalid user id entered for the project's creator.");
			}

			$project = new Model_Project(Model_Year::getCurrentYear(), $params["name"], $creator);
			$project->setSupervisor($this->auth->getUser());
			$project->save();

			$this->response->status(201);
			$this->response->body($project);
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

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /project/:id
			 * Used to update a project!
			 */

			/**
			 * Validate that the user can update this project.
			 */
			$this->validateUser(array(
				"entity" => "project/" . $project->getId(),
				"action" => ACL::UPDATE,
				"message" => "You do not have permission to update this project."
			));

			$project->update($this->request->getPostData());
			$project->save();
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /project/:id
			 * Used to delete a project.
			 */

			/**
			 * Validate that the user can delete this project.
			 */
			$this->validateUser(array(
				"entity" => "project/" . $project->getId(),
				"action" => ACL::DELETE,
				"message" => "You do not have permission to delete this project."
			));

			throw new HttpStatusException(501, "Deleting a project is coming soon.");
		}
		else
		{
			$project->getGroup();
		}

		/**
		 * GET /project/:id
		 * Used to get a project.
		 */

		$this->response->status(200);
		$this->response->body($project);
	}

	/**
	 * /project/group
	 * /project/:id/group
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_group()
	{
		$this->validateMethods(Request::POST);

		/**
		 * POST /project/:id/group
		 */

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No project id provided.");
		}

		$project = Model_Project::getById($this->request->param("id"));
		if (empty($project))
		{
			throw new HttpStatusException(404, "Project not found.");
		}

		/**
		 * Validate that the user can update this project.
		 */
		$this->validateUser(array(
			"entity" => "project/" . $project->getId(),
			"action" => ACL::UPDATE,
			"message" => "You do not have permission to update this project."
		));

		throw new HttpStatusException(501, "Adding a group to a project is coming soon.");
	}

	/**
	 * /project/rollover
	 * /project/:id/rollover
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_rollover()
	{
		$this->validateMethods(Request::POST);

		/**
		 * POST /project/:id/rollover
		 */

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No project id provided.");
		}

		$project = Model_Project::getById($this->request->param("id"));
		if (empty($project))
		{
			throw new HttpStatusException(404, "Project not found.");
		}

		/**
		 * Validate that the user is a member of staff & can update this project.
		 */
		$this->validateUser(array(
			"entity" => "project/" . $project->getId(),
			"action" => ACL::READ,
			"message" => "You do not have permission to update this project.",
			"role" => "staff"
		));

		throw new HttpStatusException(501, "Rolling over a project is coming soon.");
	}
}