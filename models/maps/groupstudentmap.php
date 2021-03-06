<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class GroupStudentMap
 * This class is designed to bring two objects together by way of a map table.
 */
class GroupStudentMap extends ModelMap
{
	/**
	 * @param Model_Group $group
	 */
	public function __construct(Model_Group $group)
	{
		parent::__construct(
			$group, "Model_User", "students",
			"SELECT `user_id` FROM `Group_Student_Map` WHERE `group_id` = ?",
			"DELETE FROM `Group_Student_Map` WHERE `group_id` = ?",
			"INSERT INTO `Group_Student_Map` (`group_id`, `user_id`) VALUES (?,?)"
		);
	}
}