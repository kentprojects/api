/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `User` (
	`user_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`uid` VARCHAR(9) NOT NULL COMMENT 'SSO UID',
	`email` VARCHAR(250) NULL COMMENT 'SSO Email',
	`first_name` VARCHAR(200) NULL,
	`last_name` VARCHAR(200) NULL,
	`role` ENUM("staff", "student") NOT NULL COMMENT 'SSO Role',
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'User Created Date',
	`lastlogin` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'User Last Login Date',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'User Updated Date',
	`status` TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`user_id`),
	UNIQUE `user_uid` (`uid`),
	UNIQUE `user_email` (`email`),
	INDEX `user_status` (`status`)
) ENGINE = InnoDB CHARACTER SET utf8;