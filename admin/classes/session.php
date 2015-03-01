<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Session
{
	/**
	 * Delete a (range of) Session key(s).
	 *
	 * @param string $key
	 * [ @param string $key ] Delete as many at once as required.
	 * @return void
	 */
	public static function delete($key)
	{
		foreach (func_get_args() as $key)
		{
			unset($_SESSION[(string)$key]);
		}
	}

	/**
	 * Destroys the session.
	 *
	 * @return void
	 */
	public static function destroy()
	{
		session_destroy();
	}

	/**
	 * Get session data.
	 *
	 * @param string $key
	 * @param mixed $default (defaults to null)
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		return (static::has($key))
			? $_SESSION[(string)$key]
			: $default;
	}

	/**
	 * Get session data and remove it afterwards.
	 *
	 * @param string $key
	 * @param mixed $default (defaults to null)
	 * @return mixed
	 */
	public static function getOnce($key, $default = null)
	{
		$value = static::get($key, $default);
		static::delete($key);
		return $value;
	}

	/**
	 * Check to see if we have session data.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public static function has($key)
	{
		return (isset($_SESSION[(string)$key]));
	}

	/**
	 * Set session data.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function set($key, $value)
	{
		$_SESSION[(string)$key] = $value;
	}
}
session_name("session");
session_start();