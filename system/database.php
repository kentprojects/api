<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

if (!defined("USE_DATABASE_STUB"))
{
	define("USE_DATABASE_STUB", false);
}

abstract class Database
{
	/**
	 * Holds the MySQLi instance
	 * @var mysqli
	 */
	private static $mysqli;
	/**
	 * A list of all database types.
	 * @var string
	 */
	private static $types = "is";

	/**
	 * Prepares a Database Query
	 *
	 * @param string $query The SQL query to run
	 * @param string $types The types of any variables
	 * @param string $format The format of the results
	 * @throws DatabaseException
	 * @return DatabaseStub|_Database_Query
	 */
	public static function prepare($query, $types = "", $format = null)
	{
		if (empty(static::$mysqli))
		{
			static::$mysqli = @new mysqli(
				config("database", "hostname"),
				config("database", "username"),
				config("database", "password"),
				config("database", "database")
			);

			if (static::$mysqli->connect_error)
			{
				throw new DatabaseException(
					static::$mysqli->connect_error, static::$mysqli->connect_errno,
					$query, $types
				);
			}

			static::$mysqli->set_charset("utf8");
		}

		if (!empty($types))
		{
			$allowed = str_split(static::$types);
			$allowedcheck = str_replace($allowed, "", $types);
			if (strlen($allowedcheck) > 0)
			{
				throw new DatabaseException("Unable to create prepared statement: There is an invalid type supplied: $allowedcheck", 0, $query, $types, $format);
			}
		}

		$statement = self::$mysqli->prepare($query);

		if ($statement === false)
		{
			throw new DatabaseException("Unable to create prepared statement: " . self::$mysqli->error, self::$mysqli->errno, $query, $types, $format);
		}

		return new _Database_Query($query, $statement, $types, $format);
	}
}

class _Database_Query
{
	/**
	 * Simple count to count the number of queries that are run.
	 * @var int
	 */
	public static $QueryCount = 0;
	/**
	 * Defines the format of the results.
	 * @var string
	 */
	protected $format;
	/**
	 * The raw SQL query to be run.
	 * @var string
	 */
	protected $query;
	/**
	 * The raw MySQLi statement object.
	 * @var mysqli_stmt
	 */
	protected $statement;
	/**
	 * Defines the parameter types.
	 * @var string
	 */
	protected $types;

	/**
	 * Creates the initial Query object.
	 *
	 * @param string $sql
	 * @param mysqli_stmt $statement
	 * @param string $types
	 * @param string $format
	 */
	public function __construct($sql, mysqli_stmt $statement, $types, $format)
	{
		$this->format = $format;
		$this->query = $sql;
		$this->statement = $statement;
		$this->types = empty($types) ? "" : (string)$types;
	}

	/**
	 * Runs the query.
	 *
	 * @throws DatabaseException
	 * @throws Exception
	 * @return _Database_Result|_Database_State
	 */
	public function execute()
	{
		$values = func_get_args();
		if (strlen($this->types) !== count($values))
		{
			throw new Exception('Invalid parameter count: expected ' . strlen($this->types) . ', got ' . count($values));
		}

		if (!empty($values))
		{
			array_unshift($values, $this->types);
			call_user_func_array(array($this->statement, 'bind_param'), self::makeParams($values));
		}

		static::$QueryCount++;

		$success = $this->statement->execute();
		if ($success === false)
		{
			throw new DatabaseException($this->statement->error, $this->statement->errno, $this->query, $this->types, $values);
		}

		$result = $this->statement->get_result();

		$return = (is_bool($result))
			? new _Database_State($this->statement)
			: new _Database_Result($result, $this->format);

		while ($this->statement->more_results() && $this->statement->next_result())
		{
			;
		}
		$this->statement->close();

		return $return;
	}

	/**
	 * @param array $args
	 * @return array
	 */
	private static function makeParams(array $args)
	{
		$refs = array();
		foreach ($args as $key => $value)
		{
			$refs[$key] = &$args[$key];
		}
		return $refs;
	}
}

class _Database_Result implements Countable
{
	/**
	 * The raw MySQLi result object.
	 * @var mysqli_result
	 */
	protected $results;

	/**
	 * Defines the format of the results.
	 * @var string
	 */
	protected $type = "object";

	/**
	 * Builds a new Database Result object.
	 *
	 * @param mysqli_result $results
	 * @param string $format
	 */
	public function __construct(mysqli_result $results, $format)
	{
		$this->results = $results;

		if (!empty($format))
		{
			$this->type = $format;
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
		$results = array();
		switch ($this->type)
		{
			case "assoc":
				while ($row = $this->results->fetch_assoc())
				{
					$results[] = $row;
				}
				break;
			case "object":
				while ($row = $this->results->fetch_object())
				{
					$results[] = $row;
				}
				break;
			default:
				while ($row = $this->results->fetch_object($this->type))
				{
					$results[] = $row;
				}
		}
		return $results;
	}

	/**
	 * Count the number of results without extracting them into an array.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->results->num_rows;
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
		$results = $this->as_assoc()->singleton();
		return (count($results) === 1) ? current($results) : null;
	}

	/**
	 * Return single values from a single result.
	 *
	 * @return mixed[]
	 */
	public function singlevals()
	{
		if ($this->count() == 0)
		{
			return array();
		}

		$results = $this->as_assoc()->all();
		if (!empty($results))
		{
			foreach ($results as &$result)
			{
				$result = current($result);
			}
		}
		return $results;
	}
}

class _Database_State
{
	/**
	 * The number of affected rows from the query.
	 * @var int
	 */
	public $affected_rows;
	/**
	 * The insert id of the row just inserted (if applicable)
	 * @var int|null
	 */
	public $insert_id;
	/**
	 * The number of affected rows from the query.
	 * @var int
	 */
	public $num_rows;

	/**
	 * Build a new Database State object.
	 *
	 * @param mysqli_stmt $statement
	 */
	public function __construct(mysqli_stmt $statement)
	{
		$this->affected_rows = $statement->affected_rows;
		$this->insert_id = $statement->insert_id;
		$this->num_rows = $statement->num_rows;
	}
}

/**
 * A simple check to ensure that the MySQLnd is installed.
 * Performance-wise, the native driver is such an improvement if you can use it you definitely should!
 */
if (!method_exists("mysqli_stmt", "get_result"))
{
	echo "mysqli_stmt::get_result is not present. Please check if mysqlnd is installed.", PHP_EOL;
	exit(1);
}