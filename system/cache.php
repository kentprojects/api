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
	 * Represents the number of seconds in a week.
	 *
	 * @var int
	 */
	const WEEK = 604800;

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

		if (empty($expires))
		{
			$expires = static::WEEK;
		}

		if (static::$memcached->add($key, $value, $expires) === true)
		{
			/**
			 * If adding it was successful, then yay!
			 */
			addStaticHeader("X-Cache-Set", "++");
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
			addStaticHeader("X-Cache-Ignored", "++");
			return $default;
		}

		$value = static::$memcached->get($key);
		if ($value !== false)
		{
			/**
			 * If that operation was okay, then return the value.
			 */
			addStaticHeader("X-Cache-Hit", "++");
			return $value;
		}

		$exception = new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
		switch ($exception->getCode())
		{
			/**
			 * If the item didn't actually exist.
			 */
			case Memcached::RES_NOTSTORED:
			case Memcached::RES_NOTFOUND:
				addStaticHeader("X-Cache-Miss", "++");
				return $default;
				break;
			/**
			 * If the item's value actually was `false`. Because that could totally happen.
			 */
			case Memcached::RES_SUCCESS:
				addStaticHeader("X-Cache-Hit", "++");
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
	 * @throws CacheException
	 * @return void
	 */
	public static function init()
	{
		$memcached = new Memcached("kentprojects");

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
			addStaticHeader("X-Cache-Ignored", "++");
			return false;
		}

		if (empty($expires))
		{
			$expires = static::WEEK;
		}

		if (static::$memcached->set($key, $value, $expires) === true)
		{
			addStaticHeader("X-Cache-Set", "++");
			return true;
		}
		/**
		 * Unlike the rest of these methods, if this `set` command fails then start panicking.
		 */
		throw new CacheException(static::$memcached->getResultMessage(), static::$memcached->getResultCode());
	}

	/**
	 * @param Model $model
	 * @throws CacheException
	 * @return void
	 */
	public static function store($model)
	{
		if (empty($model))
		{
			return;
		}

		if (!is_object($model))
		{
			throw new CacheException("Unknown variable passed to Cache::store: " . gettype($model));
		}
		if (!($model instanceof Model))
		{
			throw new CacheException("Unknown object passed to Cache::store: " . get_class($model));
		}

		static::set($model->getCacheName(), $model, 4 * static::HOUR);
	}
}

$exit = 1;
$output = array();
exec("which memcached", $output, $exit);
if ($exit > 0)
{
	error_log("Memcached is not installed on this server.");
	// throw new CacheException("Memcached is not installed on this server.", 1);
}
else
{
	// Cache::init();
}