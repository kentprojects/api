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

			if ($this->request->query("rollover") !== null)
			{
				$existingProject = Model_Project::getById($this->request->query("rollover"));
				if (empty($existingProject))
				{
					throw new HttpStatusException(404, "Rollover project not found.");
				}

				// TODO: Is user convener? That should be easy to calculate given how much we're calculating.
				$this->validateUser(array(
					"entity" => "project/" . $existingProject->getId(),
					"action" => ACL::READ,
					"message" => "You do not have permission to rollover this project.",
					"role" => "staff"
				));

				$params = array(
					"name" => $existingProject->getName()
				);

				$creator = $this->auth->getUser();
				$supervisor = $existingProject->getSupervisor();
				if (empty($supervisor))
				{
					$supervisor = $this->auth->getUser();
					// TODO: Notify convener that the supervisor has been deleted and he should take action.
				}
			}
			else
			{
				/**
				 * Validate parameters.
				 */
				$params = $this->validateParams(array(
					"name" => $this->request->post("name", false)
				));

				$creator = $supervisor = $this->auth->getUser();
			}

			$project = new Model_Project(Model_Year::getCurrentYear(), $params["name"], $creator);
			$project->setSupervisor($supervisor);
			if (!empty($existingProject))
			{
				$project->setDescription($existingProject->getDescription());
			}
			$project->save();

			$this->acl->set("project/" . $project->getId(), true, true, true, true);
			$this->acl->save();

			if (!empty($existingProject) && ($supervisor->getId() !== $this->auth->getUser()->getId()))
			{
				$supervisorACL = new ACL($supervisor);
				$supervisorACL->set("project/" . $project->getId(), true, true, true, true);
				$supervisorACL->save();
			}

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

			Model_Project::delete($project);

			$this->acl->delete("project/" . $project->getId());
			$this->acl->save();

			$this->response->status(204);
			return;
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
}