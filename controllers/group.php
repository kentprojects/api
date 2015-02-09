<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Group extends Controller
{
	/**
	 * /group
	 * /group/:id
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::PUT, Request::DELETE);

		if ($this->request->getMethod() === Request::POST)
		{
			if ($this->request->param("id") !== null)
			{
				throw new HttpStatusException(400, "You cannot create a group using an existing ID.");
			}

			if (!$this->acl->validate("group/1", ACL::DELETE))
			{
				throw new HttpStatusException(400, "You do not have permission to create a group.");
			}

			/**
			 * POST /group
			 */
			$params = $this->validateParams(array(
				"year" => $this->request->post("year", false),
				"name" => $this->request->post("name", false),
				"creator" => $this->request->post("creator", false)
			));

			$year = Model_Year::getById($params["year"]);
			if (empty($year))
			{
				throw new HttpStatusException(400, "Invalid year entered.");
			}

			$creator = Model_User::getById($params["creator"]);
			if (empty($creator))
			{
				throw new HttpStatusException(400, "Invalid user id entered for the group's creator.");
			}

			$group = new Model_Group($year, $params["name"], $creator);
			$group->save();

			$this->response->status(201);
			$this->response->body($group);
			return;
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No group id provided.");
		}

		$group = Model_Group::getById($this->request->param("id"));
		if (empty($group))
		{
			throw new HttpStatusException(404, "Group not found.");
		}

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /group/:id
			 * Update a group.
			 */
			throw new HttpStatusException(501, "Updating a group is coming soon.");
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /group/:id
			 * Update a group.
			 */
			throw new HttpStatusException(501, "Deleting a group is coming soon.");
		}

		/**
		 * GET /group/:id
		 * Get a group.
		 */
		$this->response->status(200);
		$this->response->body($group);
	}

	/**
	 * /group/student
	 * /group/:id/student
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_student()
	{
		$this->validateMethods(Request::POST, Request::DELETE);

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No group id provided.");
		}

		$group = Model_Group::getById($this->request->param("id"));
		if (empty($group))
		{
			throw new HttpStatusException(404, "Group not found.");
		}

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /group/:id/student
			 * Adding a student to a group.
			 */
			throw new HttpStatusException(501, "Adding a student to a group is coming soon.");
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /group/:id/student
			 * Removing a student from a group.
			 */
			throw new HttpStatusException(501, "Removing a student from a group is coming soon.");
		}
	}

	/**
	 * /group/supervisor
	 * /group/:id/supervisor
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_supervisor()
	{
		$this->validateMethods(Request::POST, Request::DELETE);

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No group id provided.");
		}

		$group = Model_Group::getById($this->request->param("id"));
		if (empty($group))
		{
			throw new HttpStatusException(404, "Group not found.");
		}

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /group/:id/supervisor
			 * Adding a supervisor to a group.
			 */
			throw new HttpStatusException(501, "Adding a supervisor to a group is coming soon.");
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /group/:id/supervisor
			 * Removing a supervisor from a group.
			 */
			throw new HttpStatusException(501, "Removing a supervisor from a group is coming soon.");
		}
	}
}