/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Intent` (
	`intent_id` INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT 'Intent Identifier',
	`user_id` INT UNSIGNED NOT NULL COMMENT 'Intent User Identifier',
	`handler` CHAR(32) NOT NULL COMMENT 'Intent Handler',
	`state` VARCHAR(100) NOT NULL COMMENT 'Intent State',
	`created` TIMESTAMP NOT NULL DEFAULT '2014-01-01' COMMENT 'Intent Created Date',
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Intent Updated Date',
	PRIMARY KEY (`intent_id`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `intent_state` (`state`)
) ENGINE = InnoDB CHARACTER SET utf8;