<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Query
 * Represents a query being built.
 */
final class Query
{
	const /** @noinspection SpellCheckingInspection */
		AAND = "query:where:and";
	const EQ = "query:where:equals";
	const IN = "query:where:in";
	const ON = "query:join:on";
	const OOR = "query:where:or";
	const USING = "query:join:using";

	/**
	 * @var array
	 */
	private $fields;
	/**
	 * @var array
	 */
	private $joins = array();
	/**
	 * @var string
	 */
	private $table;
	/**
	 * @var array
	 */
	private $where = array();

	/**
	 * Build a new query.
	 *
	 * @param array|string $field
	 * @param string $table
	 */
	public function __construct($field, $table)
	{
		$this->fields = is_array($field) ? $field : array($field);
		$this->table = $table;
	}

	/**
	 * Execute the query, or return the query if we're just testing this class.
	 *
	 * @param boolean $returnResults
	 * @return _Database_Result|_Database_State|_QueryStub
	 */
	public function execute($returnResults = false)
	{
		$query = "SELECT ";
		$query .= implode(
			", ",
			array_map(
				function ($field)
				{
					return "`$field`";
				},
				$this->fields
			)
		);
		$query .= " FROM `" . $this->table . "`";
		$types = "";
		$values = array();

		if (!empty($this->joins))
		{
			$query .= PHP_EOL;
			$query .= implode(
				PHP_EOL,
				array_map(
					function ($join) use (&$types, &$values)
					{
						/** @var _QueryStub $join */
						$types .= $join->types;
						$values = array_merge($values, $join->values);
						return $join->sql;
					},
					$this->joins
				)
			);
		}

		if (!empty($this->where))
		{
			$query .= " WHERE ";
			$query .= implode(
				" AND ",
				array_map(
					function ($where) use (&$types, &$values)
					{
						/** @var _QueryStub $where */
						$types .= $where->types;
						$values = array_merge($values, $where->values);
						return $where->sql;
					},
					$this->where
				)
			);
		}

		/**
		 * This returns the information back to the QueryTest.
		 * Don't ever remove this again. #badman.
		 */
		if ($returnResults === true)
		{
			return new _QueryStub($query, $types, $values);
		}

		// Log::debug($query, $types, $values);

		$statement = Database::prepare($query, $types);
		return call_user_func_array(array($statement, "execute"), $values);
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Add a new join.
	 *
	 * @param array $join
	 * @throws InvalidArgumentException
	 */
	public function join(array $join)
	{
		$join = (object)$join;

		if (empty($join->table) || empty($join->how))
		{
			throw new InvalidArgumentException("Missing table / how keys.");
		}

		$join->source = $this->table;

		$joinQuery = sprintf(" JOIN `%s`", $join->table);
		switch ($join->how)
		{
			case Query::ON:
				if (empty($join->from) || empty($join->to))
				{
					throw new InvalidArgumentException("Missing from / to for JOIN ON.");
				}
				$joinQuery .= sprintf(
					" ON `%s`.`%s` = `%s`.`%s`",
					$join->source, $join->from, $join->table, $join->to
				);
				break;
			case Query::USING:
				if (empty($join->field))
				{
					throw new InvalidArgumentException("Missing field for JOIN USING.");
				}
				$joinQuery .= sprintf(" USING (`%s`)", $join->field);
				break;
			default:
				throw new InvalidArgumentException("Invalid operator for join.");
		}

		$this->joins[] = new _QueryStub($joinQuery);
	}

	/**
	 * Add a new where clause.
	 *
	 * @param array $where
	 * @throws InvalidArgumentException
	 */
	public function where(array $where)
	{
		$where = (object)$where;

		if (empty($where->field) || (!property_exists($where, "value") && !property_exists($where, "values")))
		{
			throw new InvalidArgumentException("Missing field / value.");
		}
		elseif (property_exists($where, "value") && property_exists($where, "values"))
		{
			throw new InvalidArgumentException("You cannot have multiple value & values.");
		}
		elseif (property_exists($where, "values") && !is_array($where->values))
		{
			throw new InvalidArgumentException("Values should be an array.");
		}
		elseif (!empty($where->values) && empty($where->operator))
		{
			/** @noinspection SpellCheckingInspection */
			throw new InvalidArgumentException("Missing operator (AAND|OOR|IN) with values.");
		}
		elseif (!empty($where->operator) && (strpos($where->operator, "query:where:") !== 0))
		{
			throw new InvalidArgumentException("Invalid operator for where statement.");
		}

		$query = new _QueryStub();
		$where->table = empty($where->table) ? $this->table : $where->table;

		if (!empty($where->values))
		{
			switch ($where->operator)
			{
				case Query::AAND:
				case Query::OOR:
					$query->sql = "(";
					$query->sql .= implode(
						($where->operator === Query::AAND ? " AND " : " OR "),
						array_map(
							function ($value) use ($where, &$query)
							{
								if (!empty($where->type))
								{
									$query->types .= $where->type;
									$query->values[] = $value;
									return sprintf("`%s`.`%s` = ?", $where->table, $where->field);
								}
								else
								{
									return sprintf(
										(is_numeric($value) ? "`%s`.`%s` = %d" : "`%s`.`%s` = '%d'"),
										$where->table, $where->field, $value
									);
								}
							},
							$where->values
						)
					);
					$query->sql .= ")";
					break;
				case Query::IN:
					$query->sql = sprintf("`%s`.`%s` IN (", $where->table, $where->field);
					$query->sql .= implode(
						", ",
						array_map(
							function ($value) use (&$where, &$query)
							{
								if (!empty($where->type))
								{
									$query->types .= $where->type;
									$query->values[] = $value;
									return "?";
								}
								else
								{
									return sprintf((is_numeric($value) ? "%d" : "'%d'"), $value);
								}
							},
							$where->values
						)
					);
					$query->sql .= ")";
					break;
				default:
					trigger_error("No operator for WHERE clause with multiple values.", E_USER_ERROR);
			}
		}
		else
		{
			if (!empty($where->type))
			{
				$query->sql .= sprintf("`%s`.`%s` = ?", $where->table, $where->field);
				$query->types .= $where->type;
				$query->values[] = $where->value;
			}
			else
			{
				$query->sql = sprintf(
					(is_numeric($where->value) ? "`%s`.`%s` = %d" : "`%s`.`%s` = '%s'"),
					$where->table, $where->field, $where->value
				);
			}
		}

		$this->where[] = $query;
	}
}

/**
 * Class _QueryStub
 * Represents a piece of a query.
 */
final class _QueryStub
{
	/**
	 * @var string
	 */
	public $sql;
	/**
	 * @var string
	 */
	public $types;
	/**
	 * @var array
	 */
	public $values;

	/**
	 * @param string $sql
	 * [ @param string $types ]
	 * [ @param array $values ]
	 */
	public function __construct($sql = "", $types = "", array $values = array())
	{
		$this->sql = $sql;
		$this->types = $types;
		$this->values = $values;
	}
}