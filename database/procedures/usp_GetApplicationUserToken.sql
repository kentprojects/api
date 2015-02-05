/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
DELIMITER //
DROP PROCEDURE IF EXISTS usp_GetApplicationUserToken//
CREATE PROCEDURE usp_GetApplicationUserToken(
	IN p_application_id INT,
	IN p_user_id INT
)
	BEGIN
		/**
		 * Try to create a token, or at least refresh it.
		 */
		DECLARE p_token CHAR(32);
		SET p_token = MD5(UUID());
		INSERT INTO `Token` (`application_id`, `user_id`, `token`, `created`)
		VALUES (p_application_id, p_user_id, p_token, CURRENT_TIMESTAMP)
		ON DUPLICATE KEY UPDATE `token` = p_token;

		/**
		 * Then return all the details.
		 */
		SELECT
			`application_id`,
			`user_id`,
			`token`,
			`created`
		FROM `Token`
		WHERE `application_id` = p_application_id AND `user_id` = p_user_id;
	END
//