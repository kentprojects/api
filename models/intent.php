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
	 * Build a new Intent Model
	 *
	 * @param Model_User $user
	 * @param string $handler
	 * @throws InvalidArgumentException
	 */
	public function __construct(Model_User $user = null, $handler = null)
	{
		/**
		 * We are deliberately avoiding a Metadata class by not calling `parent`.
		 */
		if ($this->getId() === null)
		{
			if (empty($user))
			{
				throw new InvalidArgumentException("Missing Model_User argument for Model_Intent.");
			}
			if (empty($handler))
			{
				throw new InvalidArgumentException("Missing handler argument for Model_Intent.");
			}

			$this->handler = $handler;
			$this->user = $user;
		}
		else
		{
			/** @noinspection PhpParamsInspection */
			$this->user = Model_User::getById($this->user);
		}
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
		if ($this->getId() === null)
		{
			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `Intent` (`user_id`, `handler`, `created`)
				 VALUES (?, ?, CURRENT_TIMESTAMP)", "is"
			)->execute(
				$this->user->getId(), $this->handler
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			throw new LogicException("You cannot save an intent.");
		}
	}

	public function setUser(Model_User $user)
	{
		$this->user = $user;
	}
}