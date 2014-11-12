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
		if (!in_array($this->request->getMethod(), array(Request::GET, Request::POST)))
		{
			throw new HttpStatusException(501);
		}

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * Used to create a new groups!
			 */
			$this->response->status(201);
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