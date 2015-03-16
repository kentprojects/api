<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class UserYearMap
 * This class is designed to bring two objects together by way of a map table.
 * This does not extend a ModelMap since it's a little... different...
 */
final class UserYearMap implements Countable, IteratorAggregate
{
	/**
	 * @var array
	 */
	private static $fields = array(
		"year",
		"role_convener",
		"role_supervisor",
		"role_secondmarker"
	);

	/**
	 * A list of caches to clear when saving.
	 * @var string[]
	 */
	protected $cachesToClear = array();
	/**
	 * @var stdClass[]
	 */
	protected $data;
	/**
	 * @var Model_User
	 */
	protected $user;

	/**
	 * @param Model_User $user
	 */
	public function __construct(Model_User $user)
	{
		$this->data = array();
		$this->user = $user;

		$yearMap = Cache::get($this->user->getCacheName("years"));
		if (empty($yearMap))
		{
			$fields = implode(",", array_map(
				function ($field)
				{
					return "`$field`";
				},
				static::$fields
			));
			$results = Database::prepare("SELECT $fields FROM `User_Year_Map` WHERE `user_id` = ?", "i")
				->execute($this->user->getId());

			if (count($results) == 0)
			{
				return;
			}

			$yearMap = $results->all();
			Cache::set($this->user->getCacheName("years"), $yearMap, Cache::HOUR);
		}

		foreach ($yearMap as $year)
		{
			$this->data[$year->year] = $year;
		}
	}

	/**
	 * @param Model_Year $year
	 * @param array|stdClass|null $map
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function add($year, $map = null)
	{
		if (empty($year))
		{
			throw new InvalidArgumentException("Missing YEAR in " . get_called_class());
		}
		elseif (!is_object($year) && !($year instanceof Model_Year))
		{
			throw new InvalidArgumentException("Class " . get_class($year) . " is not of Model_Year.");
		}
		elseif ($year->getId() === null)
		{
			throw new InvalidArgumentException("No ID in Year object.");
		}

		if (!empty($map))
		{

			if (is_object($map) && !($map instanceof stdClass))
			{
				throw new InvalidArgumentException("Class " . get_class($map) . " is not of stdClass.");
			}
			elseif (is_array($map))
			{
				$map = (object)$map;
			}
			else
			{
				throw new InvalidArgumentException("Invalid map parameter supplied to UserYearMap::add.");
			}

			foreach ($map as $key => $value)
			{
				if ($key === "year")
				{
					throw new InvalidArgumentException("Unknown key '$key' in MAP object.");
				}
				if (!in_array($key, static::$fields))
				{
					throw new InvalidArgumentException("Unknown key '$key' in MAP object.");
				}
			}
		}
		else
		{
			$map = (object)array("year" => $year->getId());
		}

		if (array_key_exists($map->year, $this->data))
		{
			return;
		}

		foreach (static::$fields as $field)
		{
			if ($field === "year")
			{
				continue;
			}
			$map->$field = !empty($map->$field);
		}

		$this->data[$year->getId()] = $map;
		$this->cachesToClear = array_merge($this->cachesToClear, $year->clearCacheStrings());
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function clear()
	{
		foreach ($this->data as $yearId => $yearData)
		{
			$year = Model_Year::getById($yearId);
			$this->cachesToClear = array_merge($this->cachesToClear, $year->clearCacheStrings());
		}
		$this->data = array();
	}

	/**
	 * @param int $modelId
	 * @return stdClass
	 */
	public function get($modelId)
	{
		return array_key_exists($modelId, $this->data) ? $this->data[$modelId] : null;
	}

	/**
	 * @return stdClass
	 */
	public function getCurrentYear()
	{
		return $this->get((string)Model_Year::getCurrentYear());
	}

	/**
	 * Useful so the ModelMap can partake in a foreach loop.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator(array_values($this->data));
	}

	/**
	 * @param Model_Year $year
	 * @return void
	 */
	public function remove(Model_Year $year)
	{
		if (array_key_exists($year->getId(), $this->data))
		{
			unset($this->data[$year->getId()]);
			$this->cachesToClear = array_merge($this->cachesToClear, $year->clearCacheStrings());
		}
	}

	/**
	 * Render the model map.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return array_values($this->data);
	}

	/**
	 * This will replace the (?,?) values of the query with the actual number of items to insert.
	 * That way we only run one query!
	 *
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		Database::prepare("DELETE FROM `User_Year_Map` WHERE `user_id` = ?", "i")->execute($this->user->getId());
		Cache::delete($this->user->getCacheName("years"));

		if (empty($this->data))
		{
			return;
		}

		// (?,?,?,?)
		$valuesTemplate = "(" . implode(",", array_pad(array(), count(static::$fields) + 1, "?")) . ")";
		$typesTemplate = implode("", array_pad(array(), count(static::$fields) + 1, "i"));

		$queryValues = array();
		$types = "";
		$values = array();

		foreach ($this->data as $year_id => $map)
		{
			$queryValues[] = $valuesTemplate;
			$types .= $typesTemplate;
			$values[] = $this->user->getId();
			foreach ($map as $key => $value)
			{
				$values[] = intval($value);
			}
		}

		$query = implode("", array(
			"INSERT ",
			"INTO `User_Year_Map` (`user_id`, ",
			implode(", ", array_map(
				function ($field)
				{
					return "`$field`";
				},
				static::$fields
			)),
			") VALUES ",
			implode(", ", $queryValues)
		));

		$statement = Database::prepare($query, $types);
		call_user_func_array(array($statement, "execute"), $values);

		!empty($this->cachesToClear) && call_user_func_array(array("Cache", "delete"), $this->cachesToClear);
	}
}