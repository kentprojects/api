<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_Comment extends Model
{
	/**
	 * Deletes a comment.
	 *
	 * @param Model_Comment $comment
	 * @return void
	 */
	public static function delete(Model_Comment $comment)
	{
		Database::prepare("DELETE FROM `Comment` WHERE `comment_id` = ?", "i")->execute($comment->getId());
		$comment->clearCaches();
	}

	/**
	 * @param int $id
	 * @return Model_Comment
	 */
	public static function getById($id)
	{
		/** @var Model_Comment $comment */
		$comment = parent::getById($id);
		if (empty($comment))
		{
			$comment = Database::prepare(
				"SELECT
					`comment_id` AS 'id',
					`root`,
					`user_id` AS 'user',
					`comment`,
					`created`
				 FROM `Comment`
				 WHERE `comment_id` = ?
				 AND `status` = 1",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($comment);
		}
		return $comment;
	}

	/**
	 * Get a thread of comments by the root.
	 *
	 * @param string $root
	 * @return Model_Comment[]
	 */
	public static function getByRoot($root)
	{
		$ids = Cache::get(static::cacheName() . ".root." . $root);
		if (empty($ids))
		{
			$ids = Database::prepare(
				"SELECT `comment_id` FROM `Comment` WHERE `root` = ? ORDER BY `comment_id` ASC", "s"
			)->execute($root)->singlevals();
			!empty($ids) && Cache::get(static::cacheName() . ".root." . $root, 2 * Cache::HOUR);
		}
		return array_filter(array_map(array(get_called_class(), "getById"), $ids));
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $root;
	/**
	 * @var Model_User
	 */
	protected $user;
	/**
	 * @var string
	 */
	protected $comment;
	/**
	 * @var string
	 */
	protected $created;

	/**
	 * Create a new Comment.
	 *
	 * @param string $root
	 * @param Model_User $user
	 * @param string $comment
	 */
	public function __construct($root = null, Model_User $user = null, $comment = null)
	{
		if ($this->getId() === null)
		{
			if (empty($root))
			{
				trigger_error("Missing ROOT passed to the COMMENT constructor", E_USER_ERROR);
			}
			$this->root = $root;

			if (empty($user))
			{
				trigger_error("Missing USER passed to the COMMENT constructor", E_USER_ERROR);
			}
			$this->user = $user;

			if (empty($comment))
			{
				trigger_error("Missing COMMENT passed to the COMMENT constructor", E_USER_ERROR);
			}
			$this->comment = $comment;
		}
		else
		{
			/** @noinspection PhpParamsInspection */
			$this->user = Model_User::getById($this->user);
		}
		parent::__construct();
	}

	public function clearCache()
	{
		Cache::delete($this->getCacheName());
		Cache::delete(static::getCacheName() . ".root." . $this->root);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Render the comment.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"comment" => $this->comment,
				"author" => $this->user->render($request, $response, $acl, true),
				"permissions" => $acl->get($this->getEntityName()),
				"created" => $this->created
			)
		));
	}

	/**
	 * Save a comment.
	 * @return void
	 */
	public function save()
	{
		if ($this->getId() === null)
		{
			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `Comment` (`root`, `user_id`, `comment`) VALUES (?, ?, ?)", "sis"
			)->execute(
				$this->root, $this->user->getId(), $this->comment
			);
			$this->id = $result->insert_id;
			$this->created = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}
}