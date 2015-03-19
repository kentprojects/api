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

		if ($this->request->query("fields") !== null)
		{
			Model_Group::returnFields(explode(",", $this->request->query("fields")));
		}

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

		if ($this->request->query("supervisor") !== null)
		{
			/**
			 * JOIN `Project` USING (`group_id`)
			 * WHERE `Project`.`supervisor_id` = ?
			 */
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

		$groups = $query->execute()->singlevals();
		foreach ($groups as $k => $group_id)
		{
			$group = Model_Group::getById($group_id);
			$group->getProject();
			$groups[$k] = $group;
		}

		$this->response->status(200);
		$this->response->body($groups);
	}
}