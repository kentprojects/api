/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `User_Notification_Map` (
	`notification_id` INT UNSIGNED NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`read` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`notification_id`, `user_id`),
	FOREIGN KEY (`notification_id`) REFERENCES `Notification` (`notification_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8;