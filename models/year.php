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
		if (!$user->isStaff())
		{
			throw new InvalidArgumentException("This user is not a member of staff");
		}
		$statement = Database::prepare(
			"INSERT INTO `User_Year_Map` (`user_id`, `year`)
			VALUES (?, ?)",
			"ii"
		);
		return $statement->execute($user->getId(), $this->id)->affected_rows == 1;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return Model_User[]
	 */
	public function getStaff()
	{
		$users = array();
		$statement = Database::prepare("SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ?", "i");
		$user_ids = $statement->execute($this->id)->singlevals();
		foreach ($user_ids as $user_id)
		{
			$user = Model_Staff::getById($user_id);
			if (!empty($user))
			{
				$users[] = $user;
			}
		}
		return $users;
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
		if (!$user->isStaff())
		{
			throw new InvalidArgumentException("This user is not a member of staff");
		}
		$statement = Database::prepare(
			"DELETE FROM `User_Year_Map` WHERE `user_id` = ? AND `year` = ?", "ii"
		);
		return $statement->execute($user->getId(), $this->id)->affected_rows == 1;
	}
}