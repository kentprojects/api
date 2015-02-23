<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ProjectSupervisorMap
 * This class is designed to bring two objects together by way of a map table.
 */
class ProjectSupervisorMap extends ModelMap
{
	/**
	 * @param Model_Project $project
	 */
	public function __construct(Model_Project $project)
	{
		parent::__construct(
			$project, "Model_User",
			"SELECT `user_id` FROM `Project_Supervisor_Map` WHERE `project_id` = ?",
			"DELETE FROM `Project_Supervisor_Map` WHERE `project_id` = ?",
			"INSERT INTO `Project_Supervisor_Map` (`project_id`, `user_id`) VALUES (?,?)"
		);
	}
}