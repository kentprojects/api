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
	 * @param Model_User $user
	 * @return bool
	 */
	public function addStaff(Model_User $user)
	{
		/**
		 * When you run an INSERT, UPDATE or DELETE, you will get a _Database_State back instead of a _Database_Result.
		 * You can use this to determine if the query was successful or not.
		 * Also, $user->isStaff() is valid. Just sayin'.
		 * Also also, delete this.
		 */
	}

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

	/**
	 * @param Model_User $user
	 * @return bool
	 */
	public function removeStaff(Model_User $user)
	{
		/**
		 * When you run an INSERT, UPDATE or DELETE, you will get a _Database_State back instead of a _Database_Result.
		 * You can use this to determine if the query was successful or not.
		 * Also, $user->isStaff() is valid. Just sayin'.
		 * Also also, delete this.
		 */
	}
}