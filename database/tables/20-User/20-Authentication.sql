/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Authentication` (
	`user_id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
	`token` CHAR(32) NOT NULL,
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Authentication Token Created Date',
	PRIMARY KEY (`user_id`, `token`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE `authentication_token` (`token`)
) ENGINE = InnoDB CHARACTER SET utf8;