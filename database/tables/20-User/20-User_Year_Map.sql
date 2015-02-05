/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `User_Year_Map` (
	`year` INT(4) UNSIGNED NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`role_convener` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'User is convener for this year.',
	`role_supervisor` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'User is supervisor for this year.',
	`role_secondmarker` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'User is second marker for this year.',
	PRIMARY KEY (`user_id`, `year`),
	FOREIGN KEY (`user_id`) REFERENCES `User` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`year`) REFERENCES `Year` (`year`) ON UPDATE CASCADE ON DELETE CASCADE,
	INDEX `year_role_convener` (`year`, `role_convener`),
	INDEX `year_role_supervisor` (`year`, `role_supervisor`),
	INDEX `year_role_secondmarker` (`year`, `role_secondmarker`)
) ENGINE = InnoDB CHARACTER SET utf8;