/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
INSERT INTO `Application` (`application_id`, `key`, `secret`, `name`, `created`)
VALUES
	(1, '77bf0b0815ce058841d74298394643ab', '7ede1f827d744b39666214441122764c', 'Frontend', CURRENT_TIMESTAMP),
	(2, 'ad7921ce757a74d8676c9140ec498003', 'be0855399d72ad351807f3eeecec5ade', 'PHPUnit', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `application_id` = `application_id`, `key` = VALUES(`key`), `secret` = VALUES(`secret`),
	`name` = VALUES(`name`), `created` = VALUES(`created`);

INSERT INTO `Year` (`year`)
VALUES
	(2013), (2014)
ON DUPLICATE KEY UPDATE `year` = `year`;

INSERT INTO `User` (`user_id`, `email`, `first_name`, `last_name`, `role`, `created`, `updated`)
VALUES
	(1, 'J.C.Hernandez-Castro@kent.ac.uk', 'Julio', 'Hernandez-Castro', 'staff', '2014-11-21 18:38:56',
	 '2014-12-16 16:46:58'),
	(2, 'J.S.Crawford@kent.ac.uk', 'John', 'Crawford', 'staff', '2014-11-21 21:31:46', '2014-12-16 16:47:03'),
	(3, 'mh471@kent.ac.uk', 'Matt', 'House', 'student', '2014-11-21 21:31:52', '2014-12-16 16:47:06'),
	(4, 'jsd24@kent.ac.uk', 'James', 'Dryden', 'student', '2014-11-27 19:19:35', '2014-12-16 16:47:09'),
	(5, 'mjw59@kent.ac.uk', 'Matthew', 'Weeks', 'student', '2014-11-27 20:12:15', '2014-12-16 16:47:09'),
	(6, 'supervisor2@kent.ac.uk', 'Stuart', 'Supervisor', 'staff', '2014-11-28 11:10:05', '2014-12-16 16:47:14')
ON DUPLICATE KEY UPDATE `user_id` = `user_id`, `email` = `email`, `first_name` = VALUES(`first_name`),
	`last_name` = VALUES(`last_name`), `role` = `role`, `created` = `created`, `updated` = `updated`;

INSERT INTO `Token` (`application_id`, `user_id`, `token`, `created`, `updated`)
VALUES
	(2, 1, 'daa4ed4e5994c355197cc17bb52bf0d9', '2015-02-24 23:31:25', '2015-02-24 23:31:25'),
	(2, 2, 'e529609067c6dd7fcb1e744f3f634adf', '2015-02-24 23:31:25', '2015-02-24 23:31:25'),
	(2, 3, '3865caf68614ce90f15c5f77cdbbb8b9', '2015-02-24 23:31:25', '2015-02-24 23:31:25')
ON DUPLICATE KEY UPDATE `application_id` = `application_id`, `user_id` = `user_id`, `token` = VALUES(`token`),
	`created` = VALUES(`created`), `updated` = VALUES(`updated`);

INSERT INTO `User_Year_Map` (`year`, `user_id`, `role_convener`, `role_supervisor`, `role_secondmarker`)
VALUES
	(2014, 1, TRUE, FALSE, FALSE),
	(2014, 2, FALSE, TRUE, TRUE),
	(2014, 3, FALSE, FALSE, FALSE),
	(2014, 4, FALSE, FALSE, FALSE),
	(2014, 5, FALSE, FALSE, FALSE),
	(2014, 6, FALSE, TRUE, TRUE)
ON DUPLICATE KEY UPDATE `year` = `year`, `user_id` = `user_id`, `role_convener` = VALUES(`role_convener`),
	`role_supervisor` = VALUES(`role_supervisor`), `role_secondmarker` = VALUES(`role_secondmarker`);

INSERT INTO `Group` (`group_id`, `year`, `name`, `creator_id`, `created`)
VALUES
	(1, 2014, 'The Master Commanders', 3, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `group_id` = `group_id`, `year` = VALUES(`year`), `name` = VALUES(`name`),
	`creator_id` = VALUES(`creator_id`), `created` = VALUES(`created`);

INSERT INTO `Group_Student_Map` (`group_id`, `user_id`)
VALUES
	(1, 3), (1, 4), (1, 5)
ON DUPLICATE KEY UPDATE `group_id` = `group_id`, `user_id` = `user_id`;

INSERT INTO `Project` (`project_id`, `year`, `group_id`, `name`, `creator_id`, `supervisor_id`, `created`)
VALUES
	(1, 2014, 1, 'Student Project Support System', 3, 2, CURRENT_TIMESTAMP),
	(2, 2014, NULL, 'Kettle Project', 1, 2, CURRENT_TIMESTAMP),
	(3, 2014, NULL, 'Flying Helicopter Drones', 1, 6, CURRENT_TIMESTAMP),
	(4, 2014, NULL, 'Clever Dashboard', 1, 6, CURRENT_TIMESTAMP),
	(5, 2014, NULL, 'CS Kent Website Improvements', 1, 6, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `group_id` = `group_id`, `year` = VALUES(`year`), `name` = VALUES(`name`),
	`creator_id` = VALUES(`creator_id`), `supervisor_id` = VALUES(`supervisor_id`),
	`created` = VALUES(`created`);

INSERT INTO `Project_Supervisor_Map` (`project_id`, `user_id`)
VALUES
	(1, 2)
ON DUPLICATE KEY UPDATE `project_id` = `project_id`, `user_id` = `user_id`;

INSERT INTO `Metadata` (`root`, `key`, `value`) VALUES
	('Model/Application/1', 'contact_email', 'developers@kentprojects.com'),
	('Model/Application/2', 'contact_email', 'developers@kentprojects.com'),
	('Model/User/2', 'interests', 'make tea'),
	('Model/User/2', 'interests', 'refresh tea')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

# Everyone can read and update themselves.
INSERT INTO `ACL` (`user_id`, `entity`, `read`, `update`)
	SELECT `user_id`, CONCAT('user/', `user_id`), 1, 1 FROM `User`;
# Everyone can create and read comments.
INSERT INTO `ACL` (`user_id`, `entity`, `create`, `read`)
	SELECT `user_id`, 'comment', 1, 1 FROM `User`;
# Everyone can read group profiles.
INSERT INTO `ACL` (`user_id`, `entity`, `read`)
	SELECT `user_id`, 'group', 1 FROM `User`;
# Everyone can read project profiles.
INSERT INTO `ACL` (`user_id`, `entity`, `read`)
	SELECT `user_id`, 'project', 1 FROM `User`;
# Everyone can read user profiles.
INSERT INTO `ACL` (`user_id`, `entity`, `read`)
	SELECT `user_id`, 'user', 1 FROM `User`;

# The convener can do everything.
INSERT IGNORE INTO `ACL` (`user_id`, `entity`, `create`, `read`, `update`, `delete`)
VALUES
	(1, 'comment', 1, 1, 1, 1),
	(1, 'group', 1, 1, 1, 1),
	(1, 'project', 1, 1, 1, 1),
	(1, 'user', 1, 1, 1, 1);