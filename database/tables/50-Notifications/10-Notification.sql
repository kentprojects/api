/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Notification` (
	`notification_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`type` VARCHAR(200) NOT NULL,
	`actor_id` INT UNSIGNED NOT NULL,
	`group_id` INT UNSIGNED NULL,
	`intent_id` INT UNSIGNED NULL,
	`project_id` INT UNSIGNED NULL,
	`user_id` INT UNSIGNED NULL,
	`year` INT(4) UNSIGNED NULL,
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`notification_id`),
	FOREIGN KEY (`actor_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`group_id`) REFERENCES `Group` (`group_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`intent_id`) REFERENCES `Intent` (`intent_id`) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (`project_id`) REFERENCES `Project` (`project_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`year`) REFERENCES `Year` (`year`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `notification_type` (`type`)
) ENGINE = InnoDB CHARACTER SET utf8;