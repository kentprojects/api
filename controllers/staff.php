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
		if (!in_array($this->request->getMethod(), array(Request::GET, Request::PUT)))
		{
			throw new HttpStatusException(501);
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No staff id provided.");
		}

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
	}
}