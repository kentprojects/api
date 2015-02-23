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
				`state`,
				`created`,
				`updated`
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
	protected $state;
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
	 * @param string $state
	 * @throws InvalidArgumentException
	 */
	public function __construct(Model_User $user = null, $handler = null, $state = null)
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
			if (empty($state))
			{
				throw new InvalidArgumentException("Missing state argument for Model_Intent.");
			}

			$this->handler = $handler;
			$this->setState($state);
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
	public function getCleanState()
	{
		return $this->state;
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
	public function getState()
	{
		return "intent:state:" . $this->state;
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
				"INSERT INTO `Intent` (`user_id`, `handler`, `state`, `created`)
				 VALUES (?, ?, ?, CURRENT_TIMESTAMP)", "iss"
			)->execute(
				$this->user->getId(), $this->handler, $this->state
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			Database::prepare("UPDATE `Intent` SET `state` = ? WHERE `intent_id` = ?", "si")
				->execute($this->state, $this->id);
		}
	}

	/**
	 * @param string $state
	 */
	public function setState($state)
	{
		if (strpos($state, "intent:state:") !== 0)
		{
			throw new InvalidArgumentException("This state should be a valid Intent STATE constant.");
		}
		/**
		 * Stripping off "intent:state:".
		 */
		$this->state = str_replace("intent:state:", "", $state);
	}
}
