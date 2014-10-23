/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
DROP DATABASE IF EXISTS `kentprojects`;
CREATE DATABASE `kentprojects` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
GRANT ALL PRIVILEGES ON `kentprojects`.* TO 'kentprojects'@'localhost' IDENTIFIED BY 'declan4balls' WITH GRANT OPTION;