/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
CREATE TABLE IF NOT EXISTS `Metadata` (
	`metadata_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT 'Metadata Identifier',
	`root` VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Root object identifier',
	`key` VARCHAR(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Key of the key-value pair',
	`value` TEXT NOT NULL COMMENT 'The value of this piece of metadata.',
	PRIMARY KEY (`metadata_id`),
	INDEX `metadata_lookup` (`root`, `key`) COMMENT 'Lookup index for an objects metadata'
)ENGINE = InnoDBCHARACTER SET utf8 COLLATE utf8_unicode_ci
COMMENT 'A simple metadata table for non-unique key-value metadata pairs associated with another entity.';