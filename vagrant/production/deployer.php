<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * A simple script designed to automatically deploy KentProjects upon commits.
 */
$folders = glob("/var/www/kentprojects-*");
print_r($folders);