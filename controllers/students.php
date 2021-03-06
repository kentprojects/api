<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Students extends Controller
{
	/**
	 * /students
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET);

		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No id required.");
		}

		/**
		 * GET /students
		 * Get students by a criteria.
		 */

		if ($this->request->query("fields") !== null)
		{
			Model_User::returnFields(explode(",", $this->request->query("fields")));
		}

		/**
		 * SELECT `user_id` FROM `User`
		 * WHERE `role` = 'student' AND `status` = 1
		 */
		$query = new Query("user_id", "User");
		$query->where(array("field" => "role", "value" => "student"));
		$query->where(array("field" => "status", "value" => 1));

		if ($this->request->query("group") !== null)
		{
			/**
			 * JOIN `Group_Student_Map` USING (`user_id`)
			 * WHERE `Group_Student_Map`.`group_id` = ?
			 */
			$query->join(array(
				"table" => "Group_Student_Map",
				"how" => Query::USING,
				"field" => "user_id"
			));
			$query->where(array(
				"table" => "Group_Student_Map",
				"field" => "group_id",
				"type" => "i",
				"value" => $this->request->query("group")
			));
		}

		if ($this->request->query("supervisor") !== null)
		{
			/**
			 * JOIN `Group_Student_Map` USING (`user_id`)
			 * JOIN `Project` USING (`group_id`)
			 * WHERE `Project`.`supervisor_id` = ?
			 */
			$query->join(array(
				"table" => "Group_Student_Map",
				"how" => Query::USING,
				"field" => "user_id"
			));
			$query->join(array(
				"table" => "Project",
				"how" => Query::USING,
				"field" => "group_id"
			));
			$query->where(array(
				"table" => "Project",
				"field" => "supervisor_id",
				"type" => "i",
				"value" => $this->request->query("supervisor")
			));
		}

		if ($this->request->query("year") !== null)
		{
			/**
			 * JOIN `User_Year_Map` USING (`user_id`)
			 * WHERE `User_Year_Map`.`year` = ?
			 */
			$query->join(array(
				"table" => "User_Year_Map",
				"how" => Query::USING,
				"field" => "user_id"
			));
			$query->where(array(
				"table" => "User_Year_Map",
				"field" => "year",
				"type" => "i",
				"value" => $this->request->query("year")
			));
		}

		$users = $query->execute()->singlevals();
		foreach ($users as $k => $user_id)
		{
			$user = Model_User::getById($user_id);
			$user->getGroup();
			$users[$k] = $user;
		}

		$this->response->status(200);
		$this->response->body($users);
	}
}