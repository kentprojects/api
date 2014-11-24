<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
require_once __DIR__ . "/../functions.php";

/**
 * Print out to the stderr channel.
 */
function stderr()
{
	fwrite(STDERR, implode(" ", array_map(function ($v)
				{
					return print_r($v, true);
				}, func_get_args())) . PHP_EOL);
}

/**
 * Print out to the stdout channel.
 */
function stdout()
{
	fwrite(STDOUT, implode(" ", array_map(function ($v)
				{
					return print_r($v, true);
				}, func_get_args())) . PHP_EOL);
}