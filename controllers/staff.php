<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Staff extends Controller
{
	/**
	 * /staff
	 * /staff/:id
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		if ($this->request->param("id") !== null)
		{
			/**
			 * /staff/:id
			 */
			$this->validateMethods(Request::GET, Request::PUT, Request::DELETE);

			$user = Model_Staff::getById($this->request->param("id"));
			if (empty($user))
			{
				throw new HttpStatusException(404, "Staff member not found.");
			}

			if ($this->request->getMethod() === Request::PUT)
			{
				/**
				 * PUT /staff/:id
				 * Used to update staff!
				 */
				throw new HttpStatusException(501, "Updating a staff member is coming soon.");
			}
			elseif ($this->request->getMethod() === Request::DELETE)
			{
				/**
				 * DELETE /staff/:id
				 * Used to delete staff!
				 */
				throw new HttpStatusException(501, "Deleting a staff member is coming soon.");
			}

			/**
			 * GET /staff/:id
			 */

			$this->response->status(200);
			$this->response->body($user);
			return;
		}

		/**
		 * /staff
		 */
		$this->validateMethods(Request::GET, Request::POST);

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /staff
			 * Used to create staff!
			 */
			throw new HttpStatusException(501, "Creating a staff member is coming soon.");
		}

		/**
		 * GET /staff
		 */

		/**
		 * SELECT `user_id` FROM `User`
		 * WHERE `role` = 'staff' AND `status` = 1
		 */
		$query = new Query("user_id", "User");
		$query->where(array("field" => "role", "value" => "staff"));
		$query->where(array("field" => "status", "value" => 1));

		if ($this->request->query("year") !== null)
		{
			/**
			 * JOIN `User_Year_Map` USING (`user_id`)
			 * WHERE `User_Year_Map`.`year` = ?
			 */
			$query->join(array(
				"table" => "User_Year_Map",
				"how" => "USING",
				"field" => "user_id"
			));
			$query->where(array(
				"table" => "User_Year_Map",
				"field" => "year",
				"type" => "i",
				"value" => $this->request->query("year")
			));
		}

		if ($this->request->query("supervisor", false) === true)
		{
			/**
			 * JOIN `User_Year_Map` USING (`user_id`)
			 * WHERE `User_Year_Map`.`year` = ?
			 */
			if ($this->request->query("year") === null)
			{
				$query->join(array(
					"table" => "User_Year_Map",
					"how" => "USING",
					"field" => "user_id"
				));
			}
			$query->where(array(
				"table" => "User_Year_Map",
				"field" => "role_supervisor",
				"value" => "TRUE"
			));
		}

		$users = $query->execute()->singlevals();
		foreach ($users as $k => $user_id)
		{
			$users[$k] = Model_User::getById($user_id);
		}

		$this->response->status(200);
		$this->response->body($users);
	}
}