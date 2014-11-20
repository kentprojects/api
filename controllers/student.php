<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Student extends Controller
{
	/**
	 * /student
	 * /student/:id
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		if (!in_array($this->request->getMethod(), array(Request::GET, Request::PUT, Request::DELETE)))
		{
			throw new HttpStatusException(501);
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No student id provided.");
		}

		$user = Model_Student::getById($this->request->param("id"));
		if (empty($user))
		{
			throw new HttpStatusException(404, "Student not found.");
		}

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /student/:id
			 * Used to update the student profile.
			 */
			throw new HttpStatusException(501, "Updating student profiles is coming soon.");
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			/**
			 * DELETE /student/:id
			 * Used to delete the student.
			 */
			throw new HttpStatusException(501, "Deleting student profiles is coming soon.");
		}

		$this->response->status(200);
		$this->response->body($user);
	}
}