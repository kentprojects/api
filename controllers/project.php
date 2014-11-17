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
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST);

		if ($this->request->getMethod() === Request::POST)
		{
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
				throw new HttpStatusException(400, "Invalid user id entered for the project's creator.");
			}

			$slug = slugify($params["name"]);

			if (!Model_Project::validate($year, $slug))
			{
				throw new HttpStatusException(400, "This year already has a project with that name '" . $params["name"] . "'.");
			}

			$project = new Model_Project($year, $params["name"], $slug, $creator);
			$project->save();

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

		$this->response->status(200);
		$this->response->body($project);
	}
}