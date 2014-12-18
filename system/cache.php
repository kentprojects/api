<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Cache
{
	/**
	 * A useful prefix for most Cache keys.
	 *
	 * @var string
	 */
	const PREFIX = "kentprojects.api.";

	/**
	 * Represents the number of seconds in a minute.
	 *
	 * @var int
	 */
	const MINUTE = 60;
	/**
	 * Represents the number of seconds in an hour.
	 *
	 * @var int
	 */
	const HOUR = 3600;
	/**
	 * Represents the number of seconds in a day.
	 *
	 * @var int
	 */
	const DAY = 86400;

	/**
	 * @var Memcached
	 */
	private static $memcached;

	/**
	 * Adds an item to the cache.
	 * If an item already exists under that name, this will return false.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expires
	 * @throws CacheException
	 * @return bool
	 */
	public static function add($key, $value, $expires = null)
	{
		/**
		 * If the cache has not been initialised (correctly).
		 */
		if (empty(static::$memcached))
		{
			return false;
		}

		if (static::$memcached->add($key, $value, $expires) === true)
		{
			/**
			 * If adding it was successful, then yay!
			 */
			return true;
		}

		$exception = new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
		if ($exception->getCode() === Memcached::RES_NOTSTORED)
		{
			/**
			 * If the actual error was a calm 'NOT STORED' (because something already exists there), then relax.
			 */
			return false;
		}
		else
		{
			/**
			 * Otherwise panic. Something big went down.
			 */
			throw $exception;
		}
	}

	/**
	 * Removes an item from the cache.
	 * If that item never existed in the cache, don't panic. You'll get a `false` back.
	 *
	 * @param string $key
	 * @throws CacheException
	 * @return bool
	 */
	public static function delete($key)
	{
		/**
		 * If the cache has not been initialised (correctly).
		 */
		if (empty(static::$memcached))
		{
			return false;
		}

		if (static::$memcached->delete($key) === true)
		{
			return true;
		}

		$exception = new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
		if ($exception->getCode() === Memcached::RES_NOTFOUND)
		{
			/**
			 * If the actual error was a calm 'NOT FOUND', then relax.
			 */
			return false;
		}
		else
		{
			/**
			 * Otherwise panic. Something big went down.
			 */
			throw $exception;
		}
	}

	/**
	 * Get an item from the cache.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @throws CacheException
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		/**
		 * If the cache has not been initialised (correctly).
		 */
		if (empty(static::$memcached))
		{
			return $default;
		}

		$value = static::$memcached->get($key);
		if ($value !== false)
		{
			/**
			 * If that operation was okay, then return the value.
			 */
			return $value;
		}

		$exception = new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
		switch ($exception->getCode())
		{
			/**
			 * If the item didn't actually exist.
			 */
			case Memcached::RES_NOTSTORED:
				return $default;
				break;
			/**
			 * If the item's value actually was `false`. Because that could totally happen.
			 */
			case Memcached::RES_SUCCESS:
				return false;
				break;
			/**
			 * Otherwise panic. Something big went down.
			 */
			default:
				throw $exception;
		}
	}

	/**
	 * Get an item from the cache, then delete that item.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getOnce($key, $default = null)
	{
		$value = static::get($key, $default);
		static::delete($key);
		return $value;
	}

	/**
	 * Initialise the cache.
	 *
	 * @param string $key
	 * @throws CacheException
	 * @return void
	 */
	public static function init($key = null)
	{
		$memcached = new Memcached(coalesce($key, config("cache", "key")));

		if ($memcached->addServer(config("cache", "host"), config("cache", "port")) === false)
		{
			error_log(
				(string)new CacheException(
					static::$memcached->getResultMessage(), static::$memcached->getResultCode()
				)
			);
			return;
		}

		if ($memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true) === false)
		{
			throw new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
		}

		static::$memcached = $memcached;
	}

	/**
	 * Adds an item to the cache.
	 * If an item already exists under that name, this will be overwritten.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expires
	 * @throws CacheException
	 * @return bool
	 */
	public static function set($key, $value, $expires = null)
	{
		/**
		 * If the cache has not been initialised (correctly).
		 */
		if (empty(static::$memcached))
		{
			return false;
		}

		if (static::$memcached->set($key, $value, $expires) === true)
		{
			return true;
		}
		/**
		 * Unlike the rest of these methods, if this `set` command fails then start panicking.
		 */
		throw new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
	}
}

$exit = 1;
$output = array();
exec("which memcached", $output, $exit);
if ($exit > 0)
{
	error_log("Memcached is not installed on this server.");
	throw new CacheException("Memcached is not installed on this server.", 1);
}