/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Project` (
	`project_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`year` INT(4) UNSIGNED NOT NULL,
	`group_id` INT UNSIGNED NULL,
	`name` VARCHAR(250) NOT NULL,
	`creator_id` INT UNSIGNED NOT NULL,
	`supervisor_id` INT UNSIGNED NULL DEFAULT NULL,
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`status` TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`project_id`),
	FOREIGN KEY (`year`) REFERENCES `Year` (`year`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`group_id`) REFERENCES `Group` (`group_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`creator_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`supervisor_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE KEY `project_group` (`group_id`),
	INDEX `project_status` (`status`)
) ENGINE = InnoDB CHARACTER SET utf8;