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
		$query = new Query_Builder("project_id", "Project");
		$query->where(array("field" => "status", "value" => 1));

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

		$projects = $query->execute()->singlevals();
		foreach ($projects as $k => $project_id)
		{
			$projects[$k] = Model_Project::getById($project_id);
		}

		$this->response->status(200);
		$this->response->body($projects);
	}
}