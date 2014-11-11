<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Year extends Model_Abstract
{
	/**
	 * @param int $id
	 * @return Model_Year
	 */
	public static function getById($id)
	{
		$statement = Database::prepare("SELECT `year` AS 'id' FROM `Year` WHERE `year` = ?", "i", __CLASS__);
		return $statement->execute($id)->singleton();
	}

	/**
	 * @var int(4)
	 */
	protected $id;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Rather than return an array (which will become an object), return an int.
	 *
	 * @return int
	 */
	public function jsonSerialize()
	{
		return $this->getId();
	}
}