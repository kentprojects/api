<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

/**
 * The configuration.
 * @var array
 */
$config = parse_ini_file(__DIR__."/../config.ini", true);

/**
 * A list of all the files.
 * @var array
 */
$files = array_merge(
	glob(__DIR__ . "/tables/*/*.sql"),
	glob(__DIR__ . "/tables/*.sql"),
	glob(__DIR__ . "/alterations/*.sql")
);

/**
 * Build the mysql command that will be run.
 * @var string
 */
$mysql = sprintf(
	"mysql -h %s -u %s -p%s %s",
	$config["database"]["hostname"],
	$config["database"]["username"],
	$config["database"]["password"],
	$config["database"]["database"]
);

/**
 * Translating foreign characters to real Bash characters.
 * @var array
 */
$translate = array(
	" " => "\ "
);

/**
 * Loop through each file and use the command line tool to load them into the database!
 */
foreach($files as $file)
{
	$file = strtr($file, $translate);
	echo substr(strrchr($file, "/"), 1), PHP_EOL;
	if (false) echo "{$mysql} < {$file}",PHP_EOL;
	else passthru("{$mysql} < {$file}");
}