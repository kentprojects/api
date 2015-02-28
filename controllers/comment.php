<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Comment extends Controller
{
	/**
	 * @var string[]
	 */
	private $allowedEntities = array("group", "project", "user");

	/**
	 * /comment
	 * /comment/:id
	 *
	 * GET / POST / DELETE
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::DELETE);

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /comment
			 */

			if ($this->request->param("id") !== null)
			{
				throw new HttpStatusException(400, "You cannot create a comment with an existing ID.");
			}

			/**
			 * Validate that the user can create a group.
			 */
			$this->validateUser(array(
				"entity" => "comment",
				"action" => ACL::CREATE,
				"message" => "You do not have permission to create a comment."
			));

			/**
			 * Validate parameters.
			 */
			$params = $this->validateParams(array(
				"root" => $this->request->post("root", false),
				"comment" => $this->request->post("comment", false)
			));

			$params["root"] = $this->validateRoot($params["root"]);
			$comment = new Model_Comment($params["root"], $this->auth->getUser(), $params["comment"]);
			$comment->save();

			$this->response->status(201);
			$this->response->body($comment);
			return;
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No comment id provided.");
		}

		$comment = Model_Comment::getById($this->request->param("id"));

		if ($this->request->getMethod() === Request::DELETE)
		{
			$this->validateUser(array(
				"entity" => "comment/" . $comment->getId(),
				"action" => ACL::DELETE,
				"message" => "You do not have permission to delete this comment."
			));
			Model_Comment::delete($comment);
			$this->response->status(204);
			return;
		}

		$this->validateUser(array(
			"entity" => "comment/" . $comment->getId(),
			"action" => ACL::READ,
			"message" => "You do not have permission to read this comment."
		));

		$this->response->status(200);
		$this->response->body($comment);
	}

	/**
	 * /comment/thread
	 * /comment/:id/thread
	 *
	 * GET
	 *
	 * Get's a comment thread.
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_thread()
	{
		$this->validateMethods(Request::GET);

		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No ID should be passed to Controller_Comment::action_thread.");
		}
		elseif ($this->request->query("root") === null)
		{
			throw new HttpStatusException(400, "No ROOT query passed to Controller_Comment::action_thread.");
		}

		$root = $this->validateRoot($this->request->query("root"));

		$this->response->status(200);
		$this->response->body(Model_Comment::getByRoot($root));
	}

	/**
	 * @param $root
	 * @throws InvalidArgumentException
	 * @return string
	 */
	protected function validateRoot($root)
	{
		$split = explode("/", $root);
		if (count($split) !== 2)
		{
			throw new InvalidArgumentException("Invalid root format.");
		}

		list($entity, $id) = $split;
		if (empty($entity) || !in_array($entity, $this->allowedEntities))
		{
			throw new InvalidArgumentException("Invalid root entity.");
		}
		elseif (!is_numeric($id))
		{
			throw new InvalidArgumentException("Invalid root entity ID.");
		}

		$root = "{$entity}/{$id}";

		$this->validateUser(array(
			"entity" => $root,
			"action" => ACL::READ,
			"message" => "You do not have permission to read items related to {$root}."
		));

		return $root;
	}
}