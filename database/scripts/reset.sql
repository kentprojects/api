/**
 * @author: James Dryden <james@jdrydn.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Stolen from Wöbu. But it's okay, because I wrote it for them anyway.
 */
DELIMITER //
DROP PROCEDURE IF EXISTS usp_ClearEverything//
CREATE PROCEDURE usp_ClearEverything()
	BEGIN
		DECLARE kptable VARCHAR(150) DEFAULT NULL;
		DECLARE no_more_tables BOOLEAN DEFAULT FALSE;
		DECLARE kptables CURSOR FOR SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = 'kentprojects-dev';
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_tables = TRUE;

		SET foreign_key_checks = 0;

		OPEN kptables;
		kptables_loop: LOOP
			FETCH kptables INTO kptable;

			IF no_more_tables THEN
				CLOSE kptables;
				LEAVE kptables_loop;
			END IF;

			SET @statement = CONCAT('DROP TABLE ', kptable);
			PREPARE statement1 FROM @statement;
			EXECUTE statement1;
			DEALLOCATE PREPARE statement1;
		END LOOP;

		SET foreign_key_checks = 1;
	END//
CALL usp_ClearEverything//
DROP PROCEDURE IF EXISTS usp_ClearEverything//