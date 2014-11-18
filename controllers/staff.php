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
	 */
	public function action_index()
	{
		if ($this->request->param("id") !== null)
		{
			/**
			 * /staff/:id
			 */
			$this->validateMethods(Request::GET, Request::PUT);

			$user = Model_Staff::getById($this->request->param("id"));
			if (empty($user))
			{
				throw new HttpStatusException(404, "Staff not found.");
			}

			if ($this->request->getMethod() === Request::PUT)
			{
				/**
				 * Used to update staff!
				 */
			}

			$this->response->status(200);
			$this->response->body($user);
			return;
		}

		/**
		 * /staff
		 */
		$this->validateMethods(Request::GET);

		/**
		 * SELECT `user_id` FROM `User`
		 * WHERE `role` = 'staff' AND `status` = 1
		 */
		$query = new Query_Builder("user_id", "User");
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

		$users = $query->execute()->singlevals();
		foreach ($users as $k => $user_id)
		{
			$users[$k] = Model_User::getById($user_id);
		}

		$this->response->status(200);
		$this->response->body($users);
	}
}