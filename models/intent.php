<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_Intent extends Model
{
	/**
	 * Delete a particular intent.
	 *
	 * @param Model_Intent $intent
	 * @return void
	 */
	public static function delete(Model_Intent $intent)
	{
		$notifications = Model_Notification::getByIntent($intent);
		/** @var Model_Notification[] $notifications */
		foreach ($notifications as $k => $notification)
		{
			$notifications[$k] = new NotificationUserMap($notification);
		}

		Database::prepare("DELETE FROM `Intent` WHERE `intent_id` = ?", "i")->execute($intent->getId());

		/** @var NotificationUserMap[] $notifications */
		foreach ($notifications as $notificationUserMap)
		{
			if (count($notificationUserMap) > 0)
			{
				$notificationUserMap->clearCaches();
			}
		}

		$intent->user->clearCaches();
		$intent->clearCaches();
	}

	/**
	 * Delete any conflicting Intents with that hash.
	 *
	 * @param string $handler
	 * @param string $hash
	 * @return void
	 */
	public static function deleteByHash($handler, $hash)
	{
		Database::prepare("DELETE FROM `Intent` WHERE `handler` = ? AND `hash` = ?", "ss")
			->execute($handler, $hash);
	}

	/**
	 * Get the relevant Intent by it's ID.
	 *
	 * @param int $id
	 * @return Model_Intent
	 */
	public static function getById($id)
	{
		/** @var Model_Intent $intent */
		$intent = parent::getById($id);
		if (empty($intent))
		{
			$intent = Database::prepare(
				"SELECT
					`intent_id` AS 'id',
					`user_id` AS 'user',
					`handler`,
					`hash`,
					`state`,
					`created`,
					`updated`
				 FROM `Intent`
				 WHERE `intent_id` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			!empty($intent) && Cache::store($intent);
		}
		return $intent;
	}

	/**
	 * Get the relevant Intent by it's hash.
	 *
	 * @param Model_User $user
	 * @param string $handler
	 * @param string $hash
	 * @return int
	 */
	public static function findByHash(Model_User $user, $handler, $hash)
	{
		return Database::prepare(
			"SELECT `intent_id` FROM `Intent`
			 WHERE `handler` = ? AND `user_id` = ? AND `hash` = ? AND `state` = 'open'",
			"sis"
		)->execute($handler, $user->getId(), $hash)->singleval();
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
	protected $hash;
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

	public function getHash()
	{
		return $this->hash;
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
				"INSERT INTO `Intent` (`user_id`, `handler`, `hash`, `state`, `created`)
				 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)", "isss"
			)->execute(
				$this->user->getId(), $this->handler, $this->hash, $this->state
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			Database::prepare("UPDATE `Intent` SET `state` = ? WHERE `intent_id` = ?", "si")
				->execute($this->state, $this->id);
		}

		Cache::delete($this->getCacheName());
		$this->user->clearCaches();
	}

	/**
	 * @param string $hash
	 * @return void
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
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
