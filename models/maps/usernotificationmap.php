<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class UserNotificationMap extends ModelMap
{
	/**
	 * @param Model_User $user
	 */
	public function __construct(Model_User $user)
	{
		parent::__construct(
			$user, "Model_Notification", "notifications",
			"SELECT `notification_id`, `read` FROM `User_Notification_Map` WHERE `user_id` = ?",
			"DELETE FROM `User_Notification_Map` WHERE `user_id` = ?",
			"INSERT INTO `User_Notification_Map` (`user_id`, `notification_id`, `read`) VALUES (?,?,?)"
		);
	}

	protected function fetch()
	{
		$notificationIds = Cache::get($this->cacheName);
		if (empty($notificationIds))
		{
			$results = Database::prepare($this->getSQL, "i")->execute($this->source->getId());
			if (count($results) == 0)
			{
				return;
			}
			$notificationIds = $results->all();
			!empty($notificationIds) && Cache::set($this->cacheName, $notificationIds, Cache::HOUR);
		}

		$this->data = array();
		foreach ($notificationIds as $row)
		{
			$notification = Model_Notification::getById($row->notification_id);
			$notification->setRead($row->read);
			$this->data[$notification->getId()] = $notification;
		}
	}

	/**
	 * @return array
	 */
	public function getUnread()
	{
		return array_values(
			array_filter(
				$this->data,
				function ($notification)
				{
					/** @var Model_Notification $notification */
					return $notification->isUnread();
				}
			)
		);
	}

	/**
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		if (empty($this->clearSQL))
		{
			throw new InvalidArgumentException("Missing clearSQL for " . get_called_class());
		}

		Database::prepare($this->clearSQL, "i")->execute($this->source->getId());
		Cache::delete($this->cacheName);

		if (empty($this->data))
		{
			return;
		}

		if (!empty($this->saveSQL))
		{
			$query = array();
			$types = "";
			$values = array();

			/** @var Model_Notification $model */
			foreach ($this->data as $id => $model)
			{
				$query[] = "(?,?,?)";
				$types .= "iis";
				array_push($values, $this->source->getId(), $id, $model->getRead());
			}

			$this->clear();
			$statement = Database::prepare(str_replace("(?,?,?)", implode(", ", $query), $this->saveSQL), $types);
			call_user_func_array(array($statement, "execute"), $values);
		}
	}
}