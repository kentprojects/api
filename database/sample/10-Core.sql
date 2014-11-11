/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
INSERT INTO `Year` (`year`) VALUES (2014), (2015) ON DUPLICATE KEY UPDATE `year` = `year`;