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
		Database::prepare("DELETE FROM `Comment` WHERE `id` = ?", "i")->execute($comment->getId());
		Cache::delete($comment->getCacheName());
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
		return array_map(
			function ($commentId)
			{
				return static::getById($commentId);
			},
			Database::prepare("SELECT `comment_id` FROM `Comment` WHERE `root` = ? ORDER BY `comment_id` ASC", "s")
				->execute($root)->singlevals()
		);
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

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Export a comment.
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->validateFields(array_merge(
			parent::jsonSerialize(),
			array(
				"comment" => $this->comment,
				"author" => $this->user->jsonSimpleSerialize()
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