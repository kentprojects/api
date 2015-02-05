/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
INSERT INTO `Application` (`application_id`, `key`, `secret`, `name`, `created`) VALUES
	(1, '77bf0b0815ce058841d74298394643ab', '7ede1f827d744b39666214441122764c', 'Frontend', CURRENT_TIMESTAMP),
	(2, 'ad7921ce757a74d8676c9140ec498003', 'be0855399d72ad351807f3eeecec5ade', 'PHPUnit', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `application_id` = `application_id`, `key` = VALUES(`key`), `name` = VALUES(`name`),
	`created` = VALUES(`created`);

INSERT INTO `Year` (`year`) VALUES (2013), (2014)
ON DUPLICATE KEY UPDATE `year` = `year`;

INSERT INTO `User`
(`user_id`, `email`, `first_name`, `last_name`, `role`, `created`, `updated`)
VALUES
	(1, 'J.C.Hernandez-Castro@kent.ac.uk', 'Julio', 'Hernandez-Castro', 'staff', '2014-11-21 18:38:56',
	 '2014-12-16 16:46:58'),
	(2, 'J.S.Crawford@kent.ac.uk', 'John', 'Crawford', 'staff', '2014-11-21 21:31:46', '2014-12-16 16:47:03'),
	(3, 'mh471@kent.ac.uk', 'Matt', 'House', 'student', '2014-11-21 21:31:52', '2014-12-16 16:47:06'),
	(4, 'jsd24@kent.ac.uk', 'James', 'Dryden', 'student', '2014-11-27 19:19:35', '2014-12-16 16:47:09'),
	(5, 'supervisor2@kent.ac.uk', 'Another', 'Supervisor', 'staff', '2014-11-28 11:10:05', '2014-12-16 16:47:14')
ON DUPLICATE KEY UPDATE `user_id` = `user_id`, `email` = `email`, `first_name` = VALUES(`first_name`),
	`last_name` = VALUES(`last_name`), `role` = `role`, `created` = `created`, `updated` = `updated`;

INSERT INTO `User_Year_Map`
(`year`, `user_id`, `role_convener`, `role_supervisor`, `role_secondmarker`)
VALUES
	(2014, 1, TRUE, FALSE, FALSE),
	(2014, 2, FALSE, TRUE, TRUE),
	(2014, 3, FALSE, FALSE, FALSE),
	(2014, 4, FALSE, FALSE, FALSE),
	(2014, 5, FALSE, TRUE, TRUE);

INSERT INTO `Metadata` (`root`, `key`, `value`) VALUES
	('Model/Application/1', 'contact_email', 'developers@kentprojects.com'),
	('Model/Application/2', 'contact_email', 'developers@kentprojects.com')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);