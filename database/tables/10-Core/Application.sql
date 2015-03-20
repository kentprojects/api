/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Application` (
	`application_id` INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT 'Application Identifier',
	`key` CHAR(32) NOT NULL COMMENT 'Application Key',
	`secret` CHAR(32) NOT NULL COMMENT 'Application Secret',
	`name` VARCHAR(250) NOT NULL COMMENT 'Application Name',
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'Application Created Date',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Applications Updated Date',
	`status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Application Status',
	PRIMARY KEY (`application_id`),
	UNIQUE `application_key` (`key`)
) ENGINE = InnoDB CHARACTER SET utf8;