<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Stats extends Model
{
	protected static $queries = array(
		"total_students" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"WHERE u.`role` = 'student' AND u.`status` = 1",
			"AND uym.`year` = ?"
		),
		"total_students_in_groups" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"JOIN `Group_Student_Map` gsm USING (`user_id`)",
			"WHERE u.`role` = 'student' AND u.`status` = 1",
			"AND uym.`year` = ?"
		),
		"total_students_in_projects" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"JOIN `Group_Student_Map` gsm USING (`user_id`)",
			"JOIN `Project` p USING (`group_id`)",
			"WHERE u.`role` = 'student' AND u.`status` = 1",
			"AND uym.`year` = ?"
		),
		"total_groups" => array(
			"SELECT COUNT(g.`group_id`)", "FROM `Group` g",
			"WHERE g.`year` = ? AND g.`status` = 1"
		),
		"total_groups_with_projects" => array(
			"SELECT COUNT(g.`group_id`)", "FROM `Group` g",
			"JOIN `Project` p USING (`group_id`)",
			"WHERE g.`year` = ? AND g.`status` = 1"
		),
		"total_staff" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"WHERE u.`role` = 'staff' AND u.`status` = 1",
			"AND uym.`year` = ?"
		),
		"total_supervisors" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"WHERE u.`role` = 'staff' AND u.`status` = 1",
			"AND uym.`year` = ? AND uym.role_supervisor = TRUE"
		),
		"total_secondmarkers" => array(
			"SELECT COUNT(u.`user_id`)", "FROM `User` u",
			"JOIN `User_Year_Map` uym USING (`user_id`)",
			"WHERE u.`role` = 'staff' AND u.`status` = 1",
			"AND uym.`year` = ? AND uym.role_secondmarker = TRUE"
		),
	);

	/**
	 * Get the statistics.
	 *
	 * @param Model_Year $year
	 * @return stdClass
	 */
	public static function getForYear(Model_Year $year)
	{
		$queries = array();
		foreach (static::$queries as $name => $subQuery)
		{
			$queries[] = "(" . implode(" ", $subQuery) . ") AS '{$name}'";
		}
		$statement = Database::prepare(
			"SELECT " . implode(", ", $queries),
			implode("", array_pad(array(), count($queries), "i"))
		);
		$results = call_user_func_array(array($statement, "execute"), array_pad(array(), count($queries), (string)$year));

		return $results->singleton();
	}
}