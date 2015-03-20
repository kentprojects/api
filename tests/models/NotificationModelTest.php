<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class NotificationModelTest extends KentProjects_Model_TestBase
{
	public function testNotificationStrings()
	{
		$typeStrings = Model_Notification::getNotificationStrings();
		foreach ($typeStrings as $type => $strings)
		{
			if (!array_key_exists("default", $strings))
			{
				throw new Exception("Missing default string for notification type '{$type}'");
			}
		}
	}
}