<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Groups extends Controller
{
	/**
	 * /groups
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
		 * GET /groups
		 * Get groups by a criteria.
		 */

		/**
		 * SELECT `group_id` FROM `Group`
		 * WHERE `status` = 1
		 */
		$query = new Query("group_id", "Group");
		$query->where(array("field" => "status", "value" => 1));

		if ($this->request->query("year") !== null)
		{
			/**
			 * WHERE `Group`.`year` = ?
			 */
			$query->where(array(
				"field" => "year",
				"type" => "i",
				"value" => $this->request->query("year")
			));
		}

		$groups = $query->execute()->singlevals();
		foreach ($groups as $k => $group_id)
		{
			$groups[$k] = Model_Group::getById($group_id);
		}

		$this->response->status(200);
		$this->response->body($groups);
	}
}