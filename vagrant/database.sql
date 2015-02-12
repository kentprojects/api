/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
DROP DATABASE IF EXISTS `kentprojects`;
CREATE DATABASE `kentprojects` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
DROP DATABASE IF EXISTS `kentprojectstest`;
CREATE DATABASE `kentprojectstest` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT ALL PRIVILEGES ON `kentprojects`.* TO 'kentprojects'@'%' IDENTIFIED BY 'password' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON `kentprojectstest`.* TO 'kentprojects'@'%' IDENTIFIED BY 'password' WITH GRANT OPTION;

GRANT ALL PRIVILEGES ON `kentprojectstest`.* TO 'kentprojectstest'@'127.0.0.1' IDENTIFIED BY 'declan-four-balls'
WITH GRANT OPTION;