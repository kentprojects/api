<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * You wanna know the best thing about controlling likes? No ACLs.
 */
final class Controller_Like extends Controller
{
	use Entity;
	protected $allowedEntities = array("comment", "project");

	/**
	 * /like
	 *
	 * GET / POST / DELETE
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::DELETE);

		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "You cannot use IDs with the Like endpoint.");
		}

		$entity = $this->request->getMethod() === Request::POST
			? $this->request->post("entity") : $this->request->query("entity");

		if (empty($entity))
		{
			throw new HttpStatusException(400, "Missing entity passed to Like endpoint.");
		}

		$entity = $this->validateRoot($entity);

		if ($this->request->getMethod() === Request::POST)
		{
			if (Model_Like::has($entity, $this->auth->getUser()) == "liked")
			{
				throw new HttpStatusException(409, "You have already liked this entity.");
			}

			Model_Like::create($entity, $this->auth->getUser());

			$this->response->status(201);
			$this->response->body(array(
				"entity" => $entity,
				"liked" => true
			));
		}
		elseif ($this->request->getMethod() === Request::DELETE)
		{
			if (Model_Like::has($entity, $this->auth->getUser()) != "liked")
			{
				throw new HttpStatusException(400, "You haven't liked this entity.");
			}

			Model_Like::delete($entity, $this->auth->getUser());

			$this->response->status(200);
			$this->response->body(array(
				"entity" => $entity,
				"liked" => false
			));
		}
		else
		{
			$this->response->status(200);
			$this->response->body(array(
				"entity" => $entity,
				"count" => Model_Like::count($entity),
				"liked" => Model_Like::has($entity, $this->auth->getUser()) == "liked",
				"who" => Model_Like::who($entity)
			));
		}
	}
}