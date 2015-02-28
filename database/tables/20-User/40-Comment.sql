/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Comment` (
	`comment_id` INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT 'Comment ID',
	`root` VARCHAR(64) NOT NULL COMMENT 'Comment Root',
	`user_id` INT UNSIGNED NOT NULL COMMENT 'Comment Author',
	`comment` VARCHAR(200) NOT NULL COMMENT 'Comment String',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Comment Created Date',
	`status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Comment Status',
	PRIMARY KEY (`comment_id`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `comment_root` (`root`),
	INDEX `comment_status` (`status`)
) ENGINE = InnoDB CHARACTER SET utf8;