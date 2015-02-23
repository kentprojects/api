<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class StudentGroupMap
 * This class is designed to bring two objects together by way of a map table.
 */
class StudentGroupMap extends ModelMap
{
	/**
	 * @param Model_User $user
	 */
	public function __construct(Model_User $user)
	{
		parent::__construct(
			$user, "Model_Group",
			"SELECT `group_id` FROM `Group_Student_Map` WHERE `user_id` = ?",
			"DELETE FROM `Group_Student_Map` WHERE `user_id` = ?",
			"INSERT INTO `Group_Student_Map` (`user_id`, `group_id`) VALUES (?,?)"
		);
	}
}