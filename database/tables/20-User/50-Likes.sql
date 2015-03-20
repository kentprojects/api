/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Like` (
	`entity` VARCHAR(64) NOT NULL COMMENT 'Like Root',
	`user_id` INT UNSIGNED NOT NULL COMMENT 'Like Author',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Like Created Date',
	PRIMARY KEY (`entity`, `user_id`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8;