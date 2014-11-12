/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Project_Supervisor_Map` (
	`project_id` INT UNSIGNED NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`project_id`, `user_id`),
	FOREIGN KEY (`project_id`) REFERENCES `Project` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8;