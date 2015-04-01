<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_Year extends Model
{
	/**
	 * Create a new year!
	 *
	 * @param Model_User $user
	 * @return Model_Year $year
	 */
	public static function create(Model_User $user)
	{
		/** @var _Database_State $result */
		$result = Database::prepare("INSERT INTO `Year` (`year`) VALUES (DEFAULT(`year`));")->execute();
		$year = static::getById($result->insert_id);

		$yearUserMap = new UserYearMap($user);
		$yearUserMap->add($year, array(
			"role_convener" => 1
		));
		$yearUserMap->save();

		return $year;
	}

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
		return static::getById(static::getAcademicYearFromDate("today"));
	}

	/**
	 * A year runs from September to August.
	 * Therefore, if the month in the date is greater than or equal to September, return the year.
	 * Otherwise, return the year minus one.
	 *
	 * @param string $date
	 * @return int
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

	public function __construct()
	{
		if ($this->getId() === null)
		{
			throw new InvalidArgumentException("You cannot create a year like this.");
		}
		parent::__construct();
	}

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
	 * @return array
	 */
	public function clearCacheStrings()
	{
		return array_merge(
			parent::clearCacheStrings(),
			array(
				$this->getCacheName("conveners"),
				$this->getCacheName("secondmarkers"),
				$this->getCacheName("supervisors")
			)
		);
	}

	/**
	 * @return Model_User[]
	 */
	public function getConveners()
	{
		if (empty($this->conveners))
		{
			$userIds = Cache::get($this->getCacheName("conveners"));
			if (empty($conveners))
			{
				$userIds = Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_convener` = 1", "i"
				)->execute($this->getId())->singlevals();
				!empty($userIds) && Cache::set($this->getCacheName("conveners"), $userIds, 2 * Cache::HOUR);
			}
			$this->conveners = array_map(
				function ($userId)
				{
					return Model_User::getById($userId);
				},
				$userIds
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
			$userIds = Cache::get($this->getCacheName("secondmarkers"));
			if (empty($conveners))
			{
				$userIds = Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_secondmarker` = 1", "i"
				)->execute($this->getId())->singlevals();
				!empty($userIds) && Cache::set($this->getCacheName("secondmarkers"), $userIds, 2 * Cache::HOUR);
			}
			$this->secondmarkers = array_map(
				function ($userId)
				{
					return Model_User::getById($userId);
				},
				$userIds
			);
		}
		return $this->secondmarkers;
	}

	/**
	 * @return Model_User[]
	 */
	public function getSupervisors()
	{
		if (empty($this->supervisors))
		{
			$userIds = Cache::get($this->getCacheName("supervisors"));
			if (empty($conveners))
			{
				$userIds = Database::prepare(
					"SELECT `user_id` FROM `User_Year_Map` WHERE `year` = ? AND `role_supervisor` = 1", "i"
				)->execute($this->getId())->singlevals();
				!empty($userIds) && Cache::set($this->getCacheName("supervisors"), $userIds, 2 * Cache::HOUR);
			}
			$this->supervisors = array_map(
				function ($userId)
				{
					return Model_User::getById($userId);
				},
				$userIds
			);
		}
		return $this->supervisors;
	}

	/**
	 * Render the year.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return int|array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		if ($internal === true)
		{
			return intval($this->__toString());
		}

		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			/**
			 * Rendering a list of conveners, supervisors & secondmarkers for this year.
			 */
			array_map(
				function ($users) use ($request, &$response, $acl)
				{
					return array_map(
						function ($user) use ($request, &$response, $acl)
						{
							/** @var Model_User $user */
							return $user->render($request, $response, $acl, true);
						},
						$users
					);
				},
				array(
					"conveners" => $this->getConveners(),
					"supervisors" => $this->getSupervisors(),
					"secondmarkers" => $this->getSecondMarkers()
				)
			)
		));
	}
}