<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Comment extends Controller
{
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
			$this->validateUser(array(
				"entity" => $params["root"],
				"action" => ACL::READ,
				"message" => "You do not have permission to read items related to {$params["root"]}."
			));
			$comment = new Model_Comment($params["root"], $this->auth->getUser(), $params["comment"]);
			$comment->save();

			$this->acl->set("comment/" . $comment->getId(), false, true, true, true);
			$this->acl->save();

			if (strpos($params["root"], "group/") === 0)
			{
				$group = Model_Group::getById(str_replace("group/", "", $params["root"]));
				$tempAcl = new ACL($group->getCreator());
				$tempAcl->set("comment/" . $comment->getId(), false, true, false, true);
				$tempAcl->save();
			}
			elseif (strpos($params["root"], "project/") === 0)
			{
				$project = Model_Project::getById(str_replace("project/", "", $params["root"]));
				$tempAcl = new ACL($project->getSupervisor());
				$tempAcl->set("comment/" . $comment->getId(), false, true, false, true);
				$tempAcl->save();
			}
			elseif (strpos($params["root"], "user/") === 0)
			{
				$tempAcl = new ACL(Model_User::getById(str_replace("user/", "", $params["root"])));
				$tempAcl->set("comment/" . $comment->getId(), false, true, false, true);
				$tempAcl->save();
			}

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
			if ($this->acl->validate("comment/" . $comment->getId(), ACL::DELETE))
			{
				Model_Comment::delete($comment);
				$this->acl->delete("comment/" . $comment->getId());
				$this->acl->save();
				$this->response->status(204);
			}
			else
			{
				throw new HttpStatusException(400, "You do not have permission to delete this comment.");
			}
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
		$this->validateUser(array(
			"entity" => $root,
			"action" => ACL::READ,
			"message" => "You do not have permission to read items related to {$root}."
		));

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

		$allowedEntities = array("group", "project", "user");
		list($entity, $id) = $split;

		if (empty($entity) || !in_array($entity, $allowedEntities))
		{
			throw new InvalidArgumentException("Invalid root entity.");
		}
		elseif (!is_numeric($id))
		{
			throw new InvalidArgumentException("Invalid root entity ID.");
		}

		return "{$entity}/{$id}";
	}
}