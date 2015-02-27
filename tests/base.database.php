<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class DatabaseStub
{
	/**
	 * @var array
	 */
	private static $dataSets = array();

	/**
	 * @param string $model
	 * @return string
	 */
	public static function getIdFieldFromClass($model)
	{
		if ($model === "Model_Year")
		{
			return "year";
		}

		return strtolower(str_replace("Model_", "", $model)) . "_id";
	}

	/**
	 * @param string $model
	 * @throws DatabaseException
	 * @return array
	 */
	public static function load($model)
	{
		if (empty(static::$dataSets[$model]))
		{
			$file = strtolower(str_replace("Model_", "", $model)) . "s.json";
			if (!file_exists(__DIR__ . "/../data/" . $file))
			{
				throw new DatabaseException("DataSet not found for '{$model}' at '{$file}'.");
			}
			static::$dataSets[$model] = json_decode(file_get_contents(__DIR__ . "/../data/" . $file));
		}
		return static::$dataSets[$model];
	}

	public static function prepare(/** @noinspection PhpUnusedParameterInspection */
		$query, $types = "", $format = "")
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		array_shift($backtrace);
		$function = array_shift($backtrace);

		if ($function["class"] == "Query")
		{
			$function = array_shift($backtrace);
		}

		//throw new Exception(print_r($function, true));

		return new _Database_Query_Stub($function["class"], $function["function"], $function["args"], $format);
	}
}

class _Database_Query_Stub
{
	/**
	 * @var array
	 */
	protected $arguments;
	/**
	 * @var string
	 */
	protected $class;
	/**
	 * @var string
	 */
	protected $format;
	/**
	 * @var string
	 */
	protected $function;

	/**
	 * Creates the initial Query object.
	 * Since we're using faked data, we should load in the correct data!
	 *
	 * @param string $class
	 * @param string $function
	 * @param array $arguments
	 * @param string $format
	 * @throws DatabaseException
	 */
	public function __construct($class, $function, array $arguments, $format)
	{
		$this->arguments = $arguments;
		$this->class = $class;
		$this->format = $format;
		$this->function = $function;
	}

	/**
	 * Runs the query.
	 *
	 * @throws DatabaseException
	 * @return _Database_Result_Stub|_Database_State_Stub
	 */
	public function execute()
	{
		if (strpos($this->class, "Controller_") === 0)
		{
			return (new _Database_Controllers_Query_Stub($this))->execute();
		}

		switch ($this->function)
		{
			case "getByEmail":
				if (!in_array($this->class, array("Model_Staff", "Model_Student", "Model_User")))
				{
					throw new DatabaseException("Class {$this->class} should not be calling getByEmail.");
				}
				break;
			case "getById":
				$id = $this->arguments[0];
				if (empty($id))
				{
					throw new DatabaseException("Missing argument for {$this->function}.");
				}

				$data = DatabaseStub::load($this->class);
				if (empty($data))
				{
					return new _Database_Result_Stub($this, array());
				}

				$idField = DatabaseStub::getIdFieldFromClass($this->class);
				foreach ($data as $item)
				{
					if ($item->$idField === $id)
					{
						return new _Database_Result_Stub($this, array($item));
					}
				}
				return new _Database_Result_Stub($this, array());
				break;
			default:
				throw new DatabaseException("Method {$this->function} doesn't exist in the fake Database class.");
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @return string
	 */
	public function getFunction()
	{
		return $this->function;
	}
}

class _Database_Result_Stub implements Countable
{
	/**
	 * @var _Database_Query_Stub
	 */
	protected $query;
	/**
	 * @var array
	 */
	protected $results;
	/**
	 * @var string
	 */
	protected $type = "object";

	/**
	 * Builds a new Database Result object.
	 *
	 * @param _Database_Query_Stub $query
	 * @param array $results
	 */
	public function __construct(_Database_Query_Stub $query, array $results)
	{
		$this->query = $query;
		$this->results = $results;
		$this->type = $this->query->getFormat();

		if (empty($this->type))
		{
			$this->type = "object";
		}
	}

	/**
	 * Set the class to return results as an associative array.
	 *
	 * @return $this
	 */
	public function as_assoc()
	{
		$this->type = "assoc";
		return $this;
	}

	/**
	 * Set the class to return results as a class.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function as_class($name)
	{
		$this->type = $name;
		return $this;
	}

	/**
	 * Set the class to return results as a standard object.
	 * (This is the default)
	 *
	 * @return $this
	 */
	public function as_object()
	{
		$this->type = "object";
		return $this;
	}

	/**
	 * Return all the results as an array of the chosen type.
	 *
	 * @return array
	 */
	public function all()
	{
		switch ($this->type)
		{
			case "assoc":
				foreach ($this->results as &$result)
				{
					$result = (array)$result;
				}
				break;
			case "object":
				break;
			default:
				/** @var Model $class */
				$class = $this->query->getFormat();
				foreach ($this->results as &$result)
				{
					$result = $class::build($result, DatabaseStub::getIdFieldFromClass($this->query->getFormat()));
				}
		}
		return $this->results;
	}

	/**
	 * Count the number of results without extracting them into an array.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->results);
	}

	/**
	 * Return the single result.
	 * If there are multiple results, NULL will be returned.
	 *
	 * @return mixed|null
	 */
	public function singleton()
	{
		$results = $this->all();
		return (count($results) === 1) ? current($results) : null;
	}

	/**
	 * Return a single result from a single value.
	 * If there are multiple results, NULL will be returned.
	 *
	 * @return mixed
	 */
	public function singleval()
	{
		$results = $this->singleton();
		return (count($results) === 1) ? current($results) : null;
	}

	/**
	 * Return single values from a single result.
	 * If there are multiple results, NULL will be returned.
	 *
	 * @return mixed
	 */
	public function singlevals()
	{
		if (!empty($this->results))
		{
			foreach ($this->results as &$result)
			{
				$result = current($result);
			}
		}
		return $this->results;
	}
}

class _Database_State_Stub
{
	/**
	 * The number of affected rows from the query.
	 *
	 * @var int
	 */
	public $affected_rows;
	/**
	 * The insert id of the row just inserted (if applicable)
	 *
	 * @var int|null
	 */
	public $insert_id;
	/**
	 * The number of affected rows from the query.
	 *
	 * @var int
	 */
	public $num_rows;

	/**
	 * Build a new Database State object.
	 *
	 * @param int $insert_id
	 * @param int $affected_rows
	 * @param int $num_rows
	 */
	public function __construct($insert_id, $affected_rows, $num_rows)
	{
		$this->affected_rows = $affected_rows;
		$this->insert_id = $insert_id;
		$this->num_rows = $num_rows;
	}
}

final class _Database_Controllers_Query_Stub
{
	/**
	 * @var _Database_Query_Stub
	 */
	protected $query;

	/**
	 * @param _Database_Query_Stub $query
	 */
	public function __construct(_Database_Query_Stub $query)
	{
		$this->query = $query;
	}

	/**
	 * Runs the query for a controller.
	 *
	 * @return _Database_Result_Stub
	 */
	public function execute()
	{
		switch ($this->query->getClass())
		{
			case "Controller_Projects":
				return $this->runControllerProjects();
				break;
		}
		return null;
	}

	/**
	 * Runs the query for the projects controller.
	 *
	 * @return _Database_Result_Stub
	 */
	private function runControllerProjects()
	{
		switch ($this->query->getFunction())
		{
			case "action_index":
				$data = DatabaseStub::load($this->query->getClass());
				if (empty($data))
				{
					return new _Database_Result_Stub($this->query, array());
				}
				break;
		}
		return null;
	}
}