/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Group` (
	`group_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`year` INT(4) UNSIGNED NOT NULL,
	`name` VARCHAR(300) NOT NULL,
	`creator_id` INT UNSIGNED NOT NULL,
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'Group Created Date',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Group Updated Date',
	`status` TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`group_id`),
	FOREIGN KEY (`year`) REFERENCES `Year` (`year`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`creator_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `group_status` (`status`)
) ENGINE = InnoDB CHARACTER SET utf8;