<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * A really dumbed down class for likes.
 * Nothing fancy, no Model Maps, because time.
 */
abstract class Model_Like extends Model
{
	public static function count($entity)
	{
		$cacheKeys = static::generateCacheNames($entity);
		$count = Cache::get($cacheKeys["count"]);
		if (empty($count))
		{
			$count = Database::prepare("SELECT COUNT(`user_id`) FROM `Like` WHERE `entity` = ?", "s")
				->execute($entity)->singleval();
			!empty($count) && Cache::set($cacheKeys["count"], $count, Cache::HOUR);
		}
		return $count;
	}

	public static function create($entity, Model_User $user)
	{
		Database::prepare("INSERT INTO `Like` (`entity`, `user_id`) VALUES (?, ?)", "si")
			->execute($entity, $user->getId());
		call_user_func_array(array("Cache", "delete"), static::generateCacheNames($entity, $user->getId()));
	}

	/**
	 * @param string $entity
	 * @param int $userId
	 * @return array
	 */
	protected static function generateCacheNames($entity, $userId = null)
	{
		$cacheStrings = array(
			"count" => static::cacheName() . ".{$entity}.count",
			"who" => static::cacheName() . ".{$entity}.who"
		);

		if (!empty($userId))
		{
			$cacheStrings["liked"] = static::cacheName() . ".{$entity}.liked.{$userId}";
		}

		return $cacheStrings;
	}

	public static function has($entity, Model_User $user)
	{
		$cacheKeys = static::generateCacheNames($entity, $user->getId());
		$liked = Cache::get($cacheKeys["liked"]);
		if (empty($liked))
		{
			$singleVal = Database::prepare("SELECT 1 FROM `Like` WHERE `entity` = ? AND `user_id` = ?", "si")
				->execute($entity, $user->getId())->singleval();
			$liked = $singleVal == 1 ? "liked" : "not-liked";
			($singleVal == 1) && Cache::set($cacheKeys["liked"], $liked, Cache::HOUR);
		}
		return $liked;
	}

	public static function delete($entity, Model_User $user)
	{
		Database::prepare("DELETE FROM `Like` WHERE `entity` = ? AND `user_id` = ?", "si")
			->execute($entity, $user->getId());
		call_user_func_array(array("Cache", "delete"), static::generateCacheNames($entity, $user->getId()));
	}

	public static function who($entity)
	{
		$cacheKeys = static::generateCacheNames($entity);
		$who = Cache::get($cacheKeys["who"]);
		if (empty($who))
		{
			$who = Database::prepare("SELECT `user_id` FROM `Like` WHERE `entity` = ?", "s")
				->execute($entity)->singlevals();
			!empty($who) && Cache::set($cacheKeys["who"], $who, Cache::HOUR);
		}

		return array_filter(array_map(array("Model_User", "getById"), $who));
	}
}