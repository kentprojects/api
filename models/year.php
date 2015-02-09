<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Year extends Model
{
	/**
	 * @return Model_Year[]
	 */
	public static function getAll()
	{
		$years = array();
		foreach (Database::prepare("SELECT `year` AS 'id' FROM `Year`")->execute()->singlevals() as $year)
		{
			$years[] = static::getById($year);
		}
		return $years;
	}

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
	 * @return Model_Year
	 */
	public static function getCurrentYear()
	{
		$statement = Database::prepare("SELECT `year` FROM `Year` ORDER BY `year` DESC LIMIT 1");
		$year_id = $statement->execute()->singleval();

		return !empty($year_id) ? static::getById($year_id) : null;
	}

	/**
	 * A year runs from September to August.
	 * Therefore, if the month in the date is greater than or equal to September, return the year.
	 * Otherwise, return the year minus one.
	 *
	 * @param string $date
	 * @return Model_Year
	 */
	public static function getAcademicYearFromDate($date)
	{
		$date = strtotime($date);
		return (intval(date("n", $date)) >= 9) ? intval(date("Y", $date)) : (intval(date("Y", $date)) - 1);
	}

	/**
	 * @var int(4)
	 */
	protected $id;

	/**
	 * If someone forces the year to be a string, at least it'll become the YEAR, and not fail.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getId();
	}

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
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->validateFields(array_merge(
			parent::jsonSerialize(),
			array(
				"projects" => 0,
				"users" => 0
			)
		));
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