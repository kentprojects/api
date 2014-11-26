<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Date
{
	/**
	 * @var string
	 */
	const PRETTY = "l jS F Y";
	/**
	 * @var string
	 */
	const RELATIVE = "relative";
	/**
	 * @var string
	 */
	const STANDARD = "d M Y";
	/**
	 * @var string
	 */
	const SLASHES = "d/M/Y";
	/**
	 * @var string
	 */
	const TIMESTAMP = "Y-m-d H:i:s";

	/**
	 * @param string $style
	 * @param int $timestamp (defaults to now)
	 * @return string
	 */
	public static function format($style, $timestamp = null)
	{
		if ($style === static::RELATIVE)
		{
			$english = function ($time, $units)
			{
				if ($time == 1)
				{
					$time = 'a';
				}
				elseif ($time > 1) $units .= 's';
				return sprintf("%s %s ago", $time, $units);
			};

			if (empty($timestamp))
			{
				$timestamp = 0;
			}

			if (!is_numeric($timestamp))
			{
				$timestamp = strtotime($timestamp);
			}
			else
			{
				$timestamp = intval($timestamp);
			}

			if ($timestamp < 0)
			{
				return "Never";
			}

			$diff = time() - $timestamp;
			$min = 60;
			$hour = 60 * 60;
			$day = 60 * 60 * 24;
			$week = $day * 7;

			// If somebody messes with us and gives a future event.
			if ($diff < 0)
			{
			}

			// Like, when somebody JUST posted. Under 5 seconds.
			elseif ($diff < 5)
			{
				return 'a few moments ago';
			}

			// If it's a few seconds ago.
			elseif ($diff < $min)
			{
				return $english($diff, 'second');
			}

			// If it's a few minutes ago.
			elseif ($diff < $hour)
			{
				return $english(round($diff / $min), 'minute');
			}

			// It's a few hours ago.
			elseif ($diff < $day)
			{
				return $english(round($diff / $hour), 'hour');
			}

			// It's a few days ago.
			elseif ($diff < $week)
			{
				return $english(round($diff / $day), 'day');
			}

			// It's over a week ago or set in the future. So return a standard date.
			return date(static::STANDARD, $timestamp);
		}

		if (empty($timestamp))
		{
			$timestamp = 0;
		}

		if (!is_numeric($timestamp))
		{
			$timestamp = strtotime($timestamp);
		}
		else
		{
			$timestamp = intval($timestamp);
		}

		if ($timestamp < 0)
		{
			return "Never";
		}

		return date($style, $timestamp);
	}
}