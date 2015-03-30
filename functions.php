<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
define("APPLICATION_PATH", __DIR__);
date_default_timezone_set("Etc/UTC");
setlocale(LC_ALL, "en_GB.UTF8");

$_SERVER["PATH_INFO"] = empty($_SERVER["PATH_INFO"]) ? "/" : $_SERVER["PATH_INFO"];

require_once __DIR__ . "/exceptions.php";

/**
 * Register the autoloader so we can call on classes when we feel like it!
 */
spl_autoload_register(
/**
 * @param string $class
 * @return bool
 */
	function ($class)
	{
		$file = str_replace("_", "/", strtolower($class)) . ".php";
		$filename = null;

		/**
		 * If the word "Controller_" exists at the beginning of this class, handle it.
		 */
		if (strpos($class, "Controller_") === 0)
		{
			$filename = APPLICATION_PATH . "/controllers/" . str_replace("controller/", "", $file);
		}
		/**
		 * If the word "Model_" exists at the beginning of this class, handle it.
		 */
		elseif (strpos($class, "Model_") === 0)
		{
			$filename = APPLICATION_PATH . "/models/" . str_replace("model/", "", $file);
		}
		/**
		 * If the word "Intent_" exists at the beginning of this class, handle it.
		 */
		elseif (strpos($class, "Intent_") === 0)
		{
			$filename = APPLICATION_PATH . "/intents/" . str_replace("intent_", "", strtolower($class)) . ".php";
		}
		/**
		 * If the word "_Map" exists in this class, handle it.
		 * Checking to see if "Map" exists in general will make this function quicker for other classes!
		 */
		elseif (($class != "ModelMap") && (strpos($class, "Map") !== false) && (strpos($class, "Map") === (strlen($class) - 3)))
		{
			$filename = APPLICATION_PATH . "/models/maps/" . $file;
		}
		/**
		 * If the word "Permissions" exists in this class, handle it.
		 * Checking to see if "Permissions" exists in general will make this function quicker for other classes!
		 */
		elseif ((strpos($class, "Permissions") !== false) && (strpos($class, "Permissions") === (strlen($class) - 11)))
		{
			$filename = APPLICATION_PATH . "/models/permissions/" . $file;
		}
		/**
		 * Else this is a generic class in a folder, so go find it!
		 */
		else
		{
			$folders = array(
				APPLICATION_PATH . "/classes",
				APPLICATION_PATH . "/classes/traits",
				APPLICATION_PATH . "/system",
				APPLICATION_PATH . "/vendor"
			);

			foreach ($folders as $folder)
			{
				if (file_exists($folder . "/" . $file))
				{
					$filename = $folder . "/" . $file;
					break;
				}
			}
		}

		if (empty($filename) || !file_exists($filename))
		{
			return false;
		}

		/** @noinspection PhpIncludeInspection */
		require_once $filename;

		return class_exists($class, false);
	}
);

/**
 * Sets up the Error Handler
 * Wraps it up into a lovely catchable Exception
 */
set_error_handler(
	function ($error_no, $error_string, $error_file, $error_line, $error_context)
	{
		Log::log_error($error_no, "{$error_string} in {$error_file}:{$error_line}");
		throw new PHPException($error_no, $error_string, $error_file, $error_line, $error_context);
	}
);

/**
 * @param string $key
 * @param string $value
 * @return void
 */
function addStaticHeader($key, $value)
{
	class_exists("Response") && call_user_func_array(array("Response", "addStaticHeader"), func_get_args());
}

/**
 * Returns the first non-empty argument or NULL.
 *
 * @param mixed
 * @param mixed
 * [ @param mixed ] ...
 * @return mixed|null
 */
function coalesce($a, $b)
{
	foreach (func_get_args() as $a)
	{
		if (!empty($a))
		{
			return $a;
		}
	}

	return null;
}

/**
 * @param string|null $section
 * @param string|null $key
 * @throws InvalidArgumentException
 * @return array|string
 */
function config($section = null, $key = null)
{
	if (empty($GLOBALS["config.ini"]))
	{
		if (file_exists(__DIR__ . "/config.production.ini"))
		{
			$configFile = __DIR__ . "/config.production.ini";
		}
		elseif (file_exists(__DIR__ . "/config.ini"))
		{
			$configFile = __DIR__ . "/config.ini";
		}
		else
		{
			trigger_error("No config file found.", E_USER_ERROR);

			return null;
		}
		$GLOBALS["config.ini"] = parse_ini_file($configFile, true);
	}
	switch (func_num_args())
	{
		case 2:
			return $GLOBALS["config.ini"][$section][$key];
		case 1:
			return $GLOBALS["config.ini"][$section];
		default:
			throw new InvalidArgumentException("Invalid number of arguments for function config.");
	}
}

/**
 * A lovely long list of status codes that a response or exception could use.
 *
 * @param int $code
 * @return string
 */
function getHttpStatusForCode($code)
{
	$codes = array(
		// Continuation
		100 => "Continue",
		101 => "Switching Protocols",

		// Success
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",

		// 30X Redirection
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		306 => "(Unused)",
		307 => "Temporary Redirect",

		// 4XX Client Error
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",

		// 50X Server Error
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported"
	);

	return (isset($codes[$code])) ? $codes[$code] : "";
}

/**
 * Creates a slug of a string.
 * http://cubiq.org/the-perfect-php-clean-url-generator
 *
 * @param $str
 * @param array $replace
 * @param string $delimiter
 * @return string
 */
function slugify($str, $replace = array(), $delimiter = '-')
{
	if (!empty($replace))
	{
		$str = str_replace((array)$replace, ' ', $str);
	}

	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return (string)$clean;
}
