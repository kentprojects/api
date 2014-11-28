/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * First off, install a MySQL server.
 * Next, bind the MySQL service to the IP that you want (ideally, not a public facing one!)
 * And finally, run commands like the ones below!
 */

/**
 * Create the kentprojects database.
 */
DROP DATABASE IF EXISTS `kentprojects`;
CREATE DATABASE `kentprojects` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

/**
 * Create a user for the web server to access.
 * Locked down using an IP address.
 * Obviously the password isn't "password". We're students, not idiots.
 */
GRANT ALL PRIVILEGES ON `kentprojects`.* TO 'kentprojects'@'10.181.200.67' IDENTIFIED BY 'password' WITH GRANT OPTION;