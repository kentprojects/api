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
		return !empty($id) ? static::getById($id) : null;
	}

	/**
	 * Get a student by their ID.
	 *
	 * @param int $id
	 * @return Model_User
	 */
	public static function getById($id)
	{
		$cacheKey = static::cacheName() . "." . $id;
		$user = Cache::get($cacheKey);
		if (empty($user))
		{
			$user = Database::prepare(
				"SELECT `user_id` FROM `User` WHERE `user_id` = ? AND `role` = 'student' AND `status` = 1", "i"
			)->execute($id)->singleval();
			!empty($user) && Cache::set($cacheKey, $user, Cache::HOUR);
		}
		return !empty($user) ? static::getById($user) : null;
	}
}