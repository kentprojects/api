<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_Year extends Model
{
	/**
	 * @return Model_Year[]
	 */
	public static function getAll()
	{
		$cacheKey = static::cacheName() . ".all";
		$years = Cache::get($cacheKey);
		if (empty($years))
		{
			$years = Database::prepare("SELECT `year` FROM `Year` ORDER BY `year` ASC")->execute()->singlevals();
			Cache::set($cacheKey, $years, Cache::HOUR);
		}
		return array_map(array(__CLASS__, "getById"), $years);
	}

	/**
	 * @param int $id
	 * @return Model_Year
	 */
	public static function getById($id)
	{
		/** @var Model_Year $year */
		$year = parent::getById($id);
		if (empty($year))
		{
			$year = Database::prepare(
				"SELECT `year` AS 'id'
				 FROM `Year`
				 WHERE `year` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($year);
		}
		return $year;
	}

	/**
	 * Get the current Year.
	 * @return Model_Year
	 */
	public static function getCurrentYear()
	{
		$cacheKey = static::cacheName() . ".current";
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare("SELECT `year` FROM `Year` ORDER BY `year` DESC LIMIT 1")
				->execute()->singleval();
			Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return !empty($id) ? static::getById($id) : null;
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
	 * @var Model_User[]
	 */
	protected $conveners;
	/**
	 * @var Model_User[]
	 */
	protected $secondmarkers;
	/**
	 * @var Model_User[]
	 */
	protected $supervisors;

	/**
	 * If someone forces the year to be a string, at least it'll become the YEAR, and not fail.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->getId();
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
	 * @return Model_User[]
	 */
	public function getConveners()
	{
		if (empty($this->conveners))
		{
			$this->conveners = array_map(
				function ($convenerId)
				{
					return Model_User::getById($convenerId);
				},
				Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_convener` = 1", "i"
				)->execute($this->getId())->singlevals()
			);
		}
		return $this->conveners;
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
	public function getSecondMarkers()
	{
		if (empty($this->secondmarkers))
		{
			$this->secondmarkers = array_map(
				function ($userId)
				{
					return Model_User::getById($userId);
				},
				Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_secondmarker` = 1", "i"
				)->execute($this->getId())->singlevals()
			);
		}
		return $this->secondmarkers;
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
	 * @return Model_User[]
	 */
	public function getSupervisors()
	{
		if (empty($this->supervisors))
		{
			$this->supervisors = array_map(
				function ($userId)
				{
					return Model_User::getById($userId);
				},
				Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_supervisor` = 1", "i"
				)->execute($this->getId())->singlevals()
			);
		}
		return $this->supervisors;
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

	/**
	 * Render the year.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"projects" => 0,
				"users" => 0
			)
		));
	}
}