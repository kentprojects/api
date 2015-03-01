<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Log
{
	/**
	 * @var string
	 */
	public static $directory;
	/**
	 * A list of every log that has happened.
	 * @var array
	 */
	private static $logs = array();
	/**
	 * A list of the default error constants.
	 * Used in self::log_error.
	 * @var array
	 */
	private static $php_constants = array();

	public static function debug()
	{
		static::store("debug", func_get_args());
	}

	public static function error()
	{
		static::store("error", func_get_args());
	}

	/**
	 * Used in the production environment to log non-fatal PHP errors.
	 *
	 * @param int $phpErrorNo
	 * @param string $message
	 * @return void
	 */
	public static function log_error($phpErrorNo, $message)
	{
		if (empty(static::$php_constants))
		{
			static::$php_constants = get_defined_constants(true)["Core"];
		}

		foreach (static::$php_constants as $const => $value)
		{
			if ($value === $phpErrorNo)
			{
				static::error("PHP Error $const", $message);
				break;
			}
		}
	}

	public static function message()
	{
		static::store("message", func_get_args());
	}

	protected static function store($name, array $arguments)
	{
		if (empty(static::$logs[$name]))
		{
			static::$logs[$name] = array();
		}

		$log = new stdClass;
		$log->message = implode(" ", array_map(
			function ($v)
			{
				return print_r($v, true);
			},
			$arguments
		));
		$log->timestamp = time();

		self::$logs[$name][] = $log;
	}

	/**
	 * Writes to the file on shutdown.
	 *
	 * @return void
	 */
	public static function write()
	{
		/**
		 * If we have no logs don't bother doing anything else!
		 */
		if (count(static::$logs) === 0)
		{
			return;
		}

		foreach (static::$logs as $name => $log)
		{
			/**
			 * Format the log into a lovely formatted string.
			 */
			foreach ($log as $key => $message)
			{
				$log[$key] = "\t" . date("H:i:s", $message->timestamp) . " " .
					str_replace("\n", "\n\t\t", $message->message);
			}

			/**
			 * Adding some stuff either side for formatting purposes.
			 */
			array_unshift($log, date("Y-m-d H:i:s"));
			array_push($log, null, null);

			/**
			 * Ensure the folders and files exist.
			 */
			$fd = static::$directory . date("Y-m-d");
			$fl = "{$fd}/{$name}.log";

			{
				$code = 0;
				$output = array();
				exec("mkdir -p {$fd} && touch {$fl}", $output, $code);
				if ($code > 0)
				{
					error_log("mkdir -p {$fd} && touch {$fl} >> {$code} " . print_r($output, true));
				}
			}

			/**
			 * Open a file handle (creating the file if required) and lock it.
			 * Write all the logs to a file, adding some whitespace to the bottom.
			 * Release the lock and close the file handle.
			 */
			$fh = fopen($fl, "a");
			flock($fh, LOCK_EX);
			fwrite($fh, implode("\n", $log));
			fflush($fh);
			flock($fh, LOCK_UN);
			fclose($fh);
		}

		static::$logs = array();
	}
}
if (!empty($_SERVER["VAGRANT_ENV"]))
{
	Log::$directory = "/var/www/logs/";
}
else
{
	Log::$directory = APPLICATION_PATH . "/logs/";
}
/**
 * Initiate the write when the script has finished.
 */
register_shutdown_function(
	function ()
	{
		Log::write();
	}
);