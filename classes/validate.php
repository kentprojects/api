<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Validate
 * A class to assist in simple regular expression validation.
 * A great regex tester is online at http://regexpal.com
 */
abstract class Validate
{
	/**
	 * A list of descriptive comments to associate with these types.
	 * @var array
	 */
	protected static $descriptions = array(
		"password" => "A password should consist of 6 to 32 characters, made up of 'A-z 0-9 _ - ! ?'.",
		"username" => "A username should be lowercase, between 3 and 99 characters made up of 'a-z 0-9 _ -'."
	);

	/**
	 * List of regular expressions to test against.
	 * @var array
	 */
	protected static $regex = array(
		"email" => "/^([a-z0-9_\.\-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/i",
		"password" => "/^[A-z0-9_\-!?]{6,32}$/i",
		"slug" => "/^[a-z0-9\-]+$/",
		"url" => "/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i",
		"username" => "/^[a-z0-9\_\-]{3,99}$/i"
	);

	/**
	 * Get the description for the type.
	 *
	 * @param string $type
	 * @return string
	 */
	public final static function Description($type)
	{
		return (empty(static::$descriptions[strtolower($type)]))
			? ""
			: " " . static::$descriptions[strtolower($type)];
	}

	/**
	 * Validate an email address.
	 *
	 * @param string $email
	 * @return bool
	 */
	public final static function Email($email)
	{
		return static::validate(__METHOD__, $email);
	}

	/**
	 * Validate a password.
	 *
	 * @param string $password
	 * @return bool
	 */
	public final static function Password($password)
	{
		return static::validate(__METHOD__, $password);
	}

	/**
	 * Validate a slug.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public final static function Slug($slug)
	{
		return static::validate(__METHOD__, $slug);
	}

	/**
	 * See if we have a regex for a particular type.
	 *
	 * @param string $type
	 * @return boolean
	 */
	public final static function Type($type)
	{
		return (!empty(static::$regex[strtolower($type)]));
	}

	/**
	 * Validate a URL.
	 *
	 * @param string $url
	 * @return bool
	 */
	public final static function URL($url)
	{
		return static::validate(__METHOD__, $url);
	}

	/**
	 * Validate a username.
	 *
	 * @param string $username
	 * @return bool
	 */
	public final static function Username($username)
	{
		return static::validate(__METHOD__, $username);
	}

	/**
	 * Validate a string.
	 *
	 * @param string $name The name of the type.
	 * @param mixed $value The value of the type.
	 * @return boolean
	 */
	private final static function validate($name, $value)
	{
		if (empty($value))
		{
			return false;
		}

		$name = strtolower($name);

		if (empty(static::$regex[$name]))
		{
			trigger_error("No validation for $name.", E_USER_WARNING);
			return false;
		}

		return preg_match(static::$regex[$name], $value);
	}
}