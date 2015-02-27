<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Student extends Model
{
	/**
	 * Get a student by their Email.
	 *
	 * @param string $email
	 * @return Model_User
	 */
	public static function getByEmail($email)
	{
		$cacheKey = static::cacheName() . ".email." . $email;
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare(
				"SELECT `user_id` FROM `User` WHERE `email` = ? AND `role` = 'student' AND `status` = 1", "s"
			)->execute($email)->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return !empty($id) ? Model_User::getById($id) : null;
	}

	/**
	 * Get a student by their ID.
	 *
	 * @param int $user_id
	 * @return Model_User
	 */
	public static function getById($user_id)
	{
		$cacheKey = static::cacheName() . "." . $user_id;
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare(
				"SELECT `user_id` FROM `User` WHERE `user_id` = ? AND `role` = 'student' AND `status` = 1", "i"
			)->execute($user_id)->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return !empty($id) ? Model_User::getById($id) : null;
	}
}