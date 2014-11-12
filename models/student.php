<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Student extends Model_Abstract
{
	/**
	 * Get a student by their Email.
	 *
	 * @param string $email
	 * @return Model_User
	 */
	public static function getByEmail($email)
	{
		$statement = Database::prepare(
			"SELECT `user_id` FROM `User` WHERE `email` = ? AND `role` = 'student' AND `status` = 1", "s"
		);
		$user_id = $statement->execute($email)->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}

	/**
	 * Get a student by their ID.
	 *
	 * @param int $id
	 * @return Model_User
	 */
	public static function getById($id)
	{
		$statement = Database::prepare(
			"SELECT `user_id` FROM `User` WHERE `user_id` = ? AND `role` = 'student' AND `status` = 1", "i"
		);
		$user_id = $statement->execute($id)->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}
}