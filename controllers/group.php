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
			/**
			 * POST /group
			 */

			if ($this->request->param("id") !== null)
			{
				throw new HttpStatusException(400, "You cannot create a group using an existing ID.");
			}

			/**
			 * Validate that the user can create a group.
			 */
			$this->validateUser(array(
				"entity" => "group",
				"action" => ACL::CREATE,
				"message" => "You do not have permission to create this group."
			));

			/**
			 * Validate parameters.
			 */
			$params = $this->validateParams(array(
				"name" => $this->request->post("name", false)
			));

			$group = new Model_Group(Model_Year::getCurrentYear(), $params["name"], $this->auth->getUser());
			$group->save();

			$groupStudentMap = new GroupStudentMap($group);
			$groupStudentMap->add($this->auth->getUser());
			$groupStudentMap->save();

			$this->acl->set("group/" . $group->getId(), false, true, true, true);
			$this->acl->save();

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

			/**
			 * Validate that the user can update this group.
			 */
			$this->validateUser(array(
				"entity" => "group/" . $group->getId(),
				"action" => ACL::UPDATE,
				"message" => "You do not have permission to update this group."
			));
			throw new HttpStatusException(501, "Updating a group is coming soon.");
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /group/:id
			 * Delete a group.
			 */

			/**
			 * Validate that the user can delete this group.
			 */
			$this->validateUser(array(
				"entity" => "group/" . $group->getId(),
				"action" => ACL::DELETE,
				"message" => "You do not have permission to delete this group."
			));
			throw new HttpStatusException(501, "Deleting a group is coming soon.");
		}
		else
		{
			/**
			 * Fetch additional assets to display for a read.
			 */
			$group->getProject();
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

		/**
		 * Validate that this user is allowed to update this group's students.
		 */
		$this->validateUser(array(
			"entity" => "group/" . $group->getId(),
			"action" => ACL::UPDATE,
			"message" => "You do not have permission to update this groups' students."
		));

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

		/**
		 * Validate that this user is a member of staff and is allowed to update this group's supervisors.
		 */
		$this->validateUser(array(
			"entity" => "group/" . $group->getId(),
			"action" => ACL::UPDATE,
			"message" => "You do not have permission to update this groups' supervisors.",
			"role" => "staff"
		));

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