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
class UserYearMap implements Countable, IteratorAggregate, JsonSerializable
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

		$this->data = $results->all();
	}

	/**
	 * @param array|stdClass $map
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function add($map)
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

		if (empty($map->year))
		{
			throw new InvalidArgumentException("Missing YEAR key in MAP object.");
		}
		elseif (array_key_exists($map->year, $this->data))
		{
			return;
		}

		foreach ($map as $key => $value)
		{
			if (!in_array($key, static::$fields))
			{
				throw new InvalidArgumentException("Unknown key '$key' in MAP object.");
			}
		}
		foreach (static::$fields as $field)
		{
			if (empty($map->$field))
			{
				$map->$field = false;
			}
		}

		$this->data[$map->year] = $map;
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
		$this->data = array();
	}

	/**
	 * @return stdClass
	 */
	public function getLatestYear()
	{
		return end($this->data);
	}

	/**
	 * Useful so the ModelMap can partake in a foreach loop.
	 *
	 * @return array
	 */
	public function getIterator()
	{
		return $this->data;
	}

	/**
	 * Useful so the ModelMap can partake in json_encode.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->data;
	}

	/**
	 * @param string $year
	 * @return void
	 */
	public function remove($year)
	{
		if (array_key_exists($year, $this->data))
		{
			unset($this->data[$year]);
		}
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

		if (empty($this->data))
		{
			return;
		}

		// (?,?,?,?)
		$valuesTemplate = "(" . implode(",", array_pad(array(), count(static::$fields), "?")) . ")";
		$typesTemplate = implode("", array_pad(array(), count(static::$fields), "i"));

		$query = array();
		$types = "";
		$values = array();

		foreach ($this->data as $year_id => $map)
		{
			$queryValues[] = $valuesTemplate;
			$types .= $typesTemplate;
			foreach ($map as $key => $value)
			{
				$values[] = $value;
			}
		}

		$query = implode("", array(
			"INSERT ",
			"INTO `User_Year_Map` (",
			implode(", ", array_map(
				function ($field)
				{
					return "`$field`";
				},
				static::$fields
			)),
			") VALUES ",
			implode(", ", $query)
		));

		$statement = Database::prepare($query, $types);
		call_user_func_array(array($statement, "execute"), $values);
	}
}