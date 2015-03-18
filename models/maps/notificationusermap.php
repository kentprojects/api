<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class NotificationUserMap extends ModelMap
{
	/**
	 * @param Model_Notification $notification
	 */
	public function __construct(Model_Notification $notification)
	{
		parent::__construct(
			$notification, "Model_User", "targets",
			"SELECT `user_id` FROM `User_Notification_Map` WHERE `notification_id` = ?",
			"DELETE FROM `User_Notification_Map` WHERE `notification_id` = ?",
			"INSERT INTO `User_Notification_Map` (`notification_id`, `user_id`) VALUES (?,?)"
		);
	}

	/**
	 * @return array
	 */
	public function clearCacheStrings()
	{
		$strings = array();
		foreach ($this->data as $modelId => $model)
		{
			$strings[] = Cache::key("model") . sprintf("user.%d.notifications", $modelId);
		}
		Log::debug($strings);
		return $strings;
	}
}