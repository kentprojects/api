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
	protected $current;

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

		if (!empty($this->data))
		{
			$year = (string)Model_Year::getCurrentYear();
			foreach ($this->data as $groupId => $group)
			{
				/** @var Model_Group $group */
				if ((string)$group->getYear() == $year)
				{
					$this->current = $groupId;
				}
			}
		}
	}

	/**
	 * @return Model_Group
	 */
	public function getCurrentGroup()
	{
		return !empty($this->current) ? $this->data[$this->current] : null;
	}

	/**
	 * @return Model_Group
	 */
	public function getCurrentGroupWithProject()
	{
		if (empty($this->current))
		{
			return null;
		}
		/** @var Model_Group $group */
		$group = $this->data[$this->current];
		$group->getProject();
		return $group;
	}
}