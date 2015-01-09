<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
if (empty($GLOBALS["config.ini"]))
{
	if (file_exists(__DIR__ . "/../config.testing.ini"))
	{
		$configFile = __DIR__ . "/../config.testing.ini";
	}
	elseif (file_exists(__DIR__ . "/../config.ini"))
	{
		$configFile = __DIR__ . "/../config.ini";
	}
	else
	{
		trigger_error("No config file found.", E_USER_ERROR);
		return null;
	}
	$GLOBALS["config.ini"] = parse_ini_file($configFile, true);
	unset($configFile);
}

require_once __DIR__ . "/../functions.php";

try
{
	if (config("testing", "checkDatabase"))
	{
		Database::prepare("SELECT 1");
	}
}
catch (DatabaseException $e)
{
	echo "No database connection available.";
	exit(1);
}
catch (PHPException $e)
{
	echo "No database connection available.";
	exit(1);
}

/**
 * Print out to the stderr channel.
 *
 * @param mixed
 * [ @param mixed ] ...
 * @return void
 */
function stderr()
{
	fwrite(
		STDERR,
		implode(
			" ",
			array_map(function ($v)
			{
				return print_r($v, true);
			}, func_get_args())
		) . PHP_EOL
	);
}

/**
 * Print out to the stdout channel.
 *
 * @param mixed
 * [ @param mixed ] ...
 * @return void
 */
function stdout()
{
	fwrite(
		STDOUT,
		implode(
			" ",
			array_map(function ($v)
			{
				return print_r($v, true);
			}, func_get_args())
		) . PHP_EOL
	);
}

require_once __DIR__ . "/base/abstract.php";
require_once __DIR__ . "/base/controller.php";
require_once __DIR__ . "/base/database.php";
require_once __DIR__ . "/base/model.php";