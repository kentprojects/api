<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * A simple script to automatically deploy the repositories.
 * The permissions are all fine because the original checkout was made by the Apache user.
 * `sudo -u www-data` is a real life saver.
 */
header("Content-Type: text/plain");

$baseurl = "/var/www";
$directories = array(
	"kentprojects-api", "kentprojects-web",
	"kentprojects-api-dev", "kentprojects-web-dev",
);

foreach ($directories as $directory)
{
	echo $directory, PHP_EOL;
	passthru("cd $baseurl/$directory && git pull");
	if (file_exists("$baseurl/$directory/docs"))
	{
		passthru("cd $baseurl/$directory/docs && jekyll build");
	}
}