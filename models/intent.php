<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_Intent extends Model
{
	/**
	 * Get the relevant Intent by it's ID.
	 *
	 * @param int $id
	 * @return Model_Intent
	 */
	public static function getById($id)
	{
		return Database::prepare(
			"SELECT
				`intent_id` AS 'id',
				`user_id` AS 'user',
				`handler`,
				`created`,
				`updated`,
				`status`
			 FROM `Intent`
			 WHERE `intent_id` = ?",
			"i", __CLASS__
		)->execute($id)->singleton();
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var Model_User
	 */
	protected $user;
	/**
	 * @var string
	 */
	protected $handler;
	/**
	 * @var string
	 */
	protected $created;
	/**
	 * @var string
	 */
	protected $updated;
	/**
	 * @var int
	 */
	protected $status;

	/**
	 * Build a new Intent Model
	 */
	public function __construct()
	{
		/**
		 * We are deliberately avoiding a Metadata class.
		 */
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return str_replace("Model_", "", get_called_class()) . "/" . $this->getId();
	}

	/**
	 * @return string
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return string
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @return Model_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		if (empty($this->id))
		{
			if (empty($this->email))
			{
				throw new InvalidArgumentException("No email provided for the student.");
			}
			if (empty($this->role))
			{
				throw new InvalidArgumentException("No role provided for the student.");
			}

			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `User` (`email`, `first_name`, `last_name`, `role`, `created`)
				 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)",
				"ssss"
			)->execute(
				$this->email, $this->first_name, $this->last_name, $this->role
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			Database::prepare(
				"UPDATE `User`
				 SET `email` = ?, `first_name` = ?, `last_name` = ?
				 WHERE `user_id` = ?",
				"sssi"
			)->execute(
				$this->email, $this->first_name, $this->last_name,
				$this->id
			);
			$this->updated = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}
}