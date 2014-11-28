<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Staff extends Model_Abstract
{
	/**
	 * Get a staff by their Email.
	 *
	 * @param string $email
	 * @return Model_User
	 */
	public static function getByEmail($email)
	{
		$statement = Database::prepare(
			"SELECT `user_id` FROM `User` WHERE `email` = ? AND `role` = 'staff' AND `status` = 1", "s"
		);
		$user_id = $statement->execute($email)->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}

	/**
	 * Get a staff by their ID.
	 *
	 * @param int $id
	 * @return Model_User
	 */
	public static function getById($id)
	{
		$statement = Database::prepare(
			"SELECT `user_id` FROM `User` WHERE `user_id` = ? AND `role` = 'staff' AND `status` = 1", "i"
		);
		$user_id = $statement->execute($id)->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}
}