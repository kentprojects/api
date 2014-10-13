<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @category: API
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

/**
 * Define the relevant paths.
 */
define("APPLICATION_PATH", __DIR__);

/**
 * Register the autoloader so we can call on classes when we feel like it!
 */
spl_autoload_register(
	/**
	 * @param string $class
	 * @return string The filename where the class exists.
	 */
	function($class) {
		$file = str_replace("_", "/", strtolower($class)).".php";
		$filename = null;

		/**
		 * If the word "Exception" exists in this class, handle it.
		 */
		if (strpos($class, "Exception") !== false)
		{
			$filename = APPLICATION_PATH."/exceptions/".$file;
		}
		/**
		 * If the word "Controller_" exists in this class, handle it.
		 */
		elseif (strpos($class, "Controller_") === 0)
		{
			$filename = APPLICATION_PATH."/controllers/".str_replace("controller/", "", $file);
		}
		/**
		 * If the word "Model_" exists in this class, handle it.
		 */
		elseif (strpos($class, "Model_") === 0)
		{
			$filename = APPLICATION_PATH."/models/".str_replace("model/", "", $file);
		}
		/**
		 * Else this is a generic class in a folder, so go find it!
		 */
		else
		{
			$folders = array(
				APPLICATION_PATH."/classes",
				APPLICATION_PATH."/system",
				APPLICATION_PATH."/vendor"
			);

			foreach($folders as $folder)
			{
				if (file_exists($folder."/".$file))
				{
					$filename = $folder."/".$file;
					break;
				}
			}
		}

		if (empty($filename))
		{
			trigger_error("Class {$class} not found.", E_USER_ERROR);
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
	/**
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param string $errline
	 * @param string $errcontext
	 * @return void
	 * @throws PHPException
	 */
	function($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new PHPException($errno, $errstr, $errfile, $errline, $errcontext);
	}
);

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
		if (!empty($a))
			return $a;
	return null;
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
		505 => "HTTP Version Not Supported",

		// 70X Inexcusable
		701 => "Meh",
		702 => "Emacs",
		703 => "Explosion",

		// 71X Novelty Implementations
		710 => "PHP",
		711 => "Convenience Store",
		712 => "NoSQL",
		719 => "I am not a teapot",

		// 72X Edge Cases
		720 => "Unpossible",
		721 => "Known Unknowns",
		722 => "Unknown Unknowns",
		723 => "Tricky",
		724 => "This line should be unreachable",
		725 => "It works on my machine",
		726 => "It's a feature, not a bug",
		727 => "32 bits is plenty",

		// 73X Fucking
		731 => "Fucking Rubygems",
		732 => "Fucking Unicode",
		733 => "Fucking Deadlocks",
		734 => "Fucking Deferreds",
		735 => "Fucking IE",
		736 => "Fucking Race Conditions",
		737 => "FuckingThreadsing",
		738 => "Fucking Bundler",
		739 => "Fucking Windows",

		// 74X Meme Driven
		740 => "Computer says no",
		741 => "Compiling",
		742 => "A kitten dies",
		743 => "I thought I knew regular expressions",
		744 => "Y U NO write integration tests?",
		745 => "I don't always test me code, but when I do I do it in production",
		746 => "Missed Ballmer Peak",
		747 => "Motherfucking Snakes on the Motherfucking Plane",
		748 => "Confounded by ponies",
		749 => "Reserved for Chuck Norris"
	);
	return (isset($codes[$code])) ? $codes[$code] : "";
}