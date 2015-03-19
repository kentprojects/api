<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Projects extends Controller
{
	/**
	 * /projects
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
		 * GET /projects
		 * Get projects by a criteria.
		 */

		/**
		 * SELECT `project_id` FROM `Project`
		 * WHERE `status` = 1
		 */
		$query = new Query("project_id", "Project");
		$query->where(array("field" => "status", "value" => 1));

		if ($this->request->query("fields") !== null)
		{
			Model_Project::returnFields(explode(",", $this->request->query("fields")));
		}

		if ($this->request->query("ids") !== null)
		{
			$query->where(array(
				"field" => "project_id",
				"operator" => Query::IN,
				"type" => "i",
				"values" => explode(",", $this->request->query("ids"))
			));
		}

		if ($this->request->query("year") !== null)
		{
			/**
			 * WHERE `Project`.`year` = ?
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
			 * WHERE `Project`.`supervisor_id` = ?
			 */
			$query->where(array(
				"field" => "supervisor_id",
				"type" => "i",
				"value" => $this->request->query("supervisor")
			));
		}

		$projects = $query->execute()->singlevals();
		foreach ($projects as $k => $project_id)
		{
			$projects[$k] = Model_Project::getById($project_id);
		}

		$this->response->status(200);
		$this->response->body($projects);
	}
}