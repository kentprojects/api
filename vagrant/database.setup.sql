/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
DROP DATABASE IF EXISTS `kentprojects`;
CREATE DATABASE `kentprojects` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON `kentprojects`.* TO 'kentprojects'@'127.0.0.1' IDENTIFIED BY 'declan4-for-balls' WITH GRANT OPTION;