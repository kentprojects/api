/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Metadata` (
	`id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
	`root` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`key` VARCHAR(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
	`value` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `metadata_lookup` (`root`, `key`)
) ENGINE = InnoDB CHARACTER SET utf8;