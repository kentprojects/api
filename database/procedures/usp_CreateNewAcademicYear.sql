/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
DELIMITER //
DROP PROCEDURE IF EXISTS usp_CreateNewAcademicYear//
CREATE PROCEDURE usp_CreateNewAcademicYear(
	IN p_user_id INT # The User ID of the person running this.
)
	BEGIN
		DECLARE newYear INT(4);
		DECLARE previousYear INT(4);
		DECLARE isConvener BOOLEAN;

		SET previousYear = (SELECT `year` FROM `Year` ORDER BY `year` DESC LIMIT 1);
		SET isConvener = (SELECT `role_convener` FROM `User_Year_Map` WHERE `year` = previousYear
			AND `user_id` = p_user_id);

		IF (isConvener IS TRUE) THEN
			INSERT INTO `Year` (`year`) VALUES (DEFAULT(`year`));
			SET newYear = LAST_INSERT_ID();
			INSERT INTO `User_Year_Map` (`year`, `user_id`, `role_convener`) VALUES (newYear, p_user_id, TRUE);
			SELECT newYear;
		ELSE
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'You must be a convener to create a new year.';
		END IF;
	END
//