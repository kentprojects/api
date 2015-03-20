/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Token` (
	`token_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`application_id` INT UNSIGNED NOT NULL COMMENT 'Token Application Identifier',
	`user_id` INT UNSIGNED NOT NULL COMMENT 'Token User Identifier',
	`token` CHAR(32) NOT NULL COMMENT 'Token Value',
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'Token Created Date',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Token Updated Date',
	PRIMARY KEY (`token_id`),
	FOREIGN KEY (`application_id`) REFERENCES `Application` (`application_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE `token_link` (`application_id`, `user_id`),
	UNIQUE `token_token` (`token`)
) ENGINE = InnoDB CHARACTER SET utf8;