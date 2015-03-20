<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ShortUrl
 * Designed to take IDs and translate them to short URLs.
 * Taken from a piece of code I wrote for the "Wöbu / Grid-API / actions / shorturl.js".
 * Adapted for PHP.
 */
final class ShortUrl
{
	private static $alphabet = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

	/**
	 * Translate a short URL to an ID.
	 *
	 * @param string $url
	 * @return int
	 */
	public static function decode($url)
	{
		if (empty($url))
		{
			return 0;
		}

		$decoded = 0;
		$length = strlen(static::$alphabet);
		$multi = 1;

		for ($i = (strlen($url) - 1); $i >= 0; $i--)
		{
			$decoded = $decoded + ($multi * strpos(static::$alphabet, $url[$i]));
			$multi = $multi * $length;
		}

		return $decoded;
	}

	/**
	 * Translate an ID to a short URL.
	 *
	 * @param int $id
	 * @return string
	 */
	public static function encode($id)
	{
		if (empty($id))
		{
			return null;
		}

		$encoded = "";
		$length = strlen(static::$alphabet);
		$num = $id;

		while ($num >= $length)
		{
			$div = intval($num / $length);
			$mod = ($num - ($length * $div));
			$encoded = static::$alphabet[$mod] . $encoded;
			$num = $div;
		}
		if (!empty($num))
		{
			$encoded = static::$alphabet[$num] . $encoded;
		}

		return $encoded;
	}
}