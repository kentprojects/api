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

		$this->response->status(200);
		$this->response->body($group);
	}
}