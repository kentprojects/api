/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `ACL` (
	`user_id` INT UNSIGNED NOT NULL COMMENT 'User Identifier',
	`entity` VARCHAR(250) NOT NULL COMMENT 'Entity Identifier',
	`create` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Can this user create?',
	`read` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Can this user read?',
	`update` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Can this user update?',
	`delete` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Can this user delete?',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'ACL Updated Date',
	PRIMARY KEY (`user_id`, `entity`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `ACL_entity` (`entity`)
) ENGINE = InnoDB CHARACTER SET utf8;