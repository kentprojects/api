<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Query
{
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
	 * @param array|string $field
	 * @param string $table
	 */
	public function __construct($field, $table)
	{
		$this->fields = is_array($field) ? $field : array($field);
		$this->table = $table;

		foreach ($this->fields as $k => $field)
		{
			$this->fields[$k] = "`$field`";
		}
	}

	/**
	 * @param bool $printQuery
	 * @return _Database_Result|_Database_State
	 */
	public function execute($printQuery = false)
	{
		$query = "SELECT " . implode(",", $this->fields) . " FROM `" . $this->table . "`";
		$types = "";
		$values = array();

		if (!empty($this->joins))
		{
			foreach ($this->joins as $join)
			{
				$joinQuery = sprintf(" JOIN `%s`", $join->table);
				if ($join->how === "USING")
				{
					$joinQuery .= sprintf(" USING (`%s`)", $join->field);
				}
				elseif ($join->how === "ON")
				{
					$joinQuery .= sprintf(
						" ON `%s`.`%s` = `%s`.`%s`",
						$join->source, $join->from, $join->table, $join->to
					);
				}
				$query .= $joinQuery;
			}
		}

		if (!empty($this->where))
		{
			$query .= " WHERE";
			foreach ($this->where as $i => $where)
			{
				if ($i > 0)
				{
					$query .= " AND";
				}

				if (!empty($where->type))
				{
					if (!empty($where->values))
					{
						switch ($where->operator)
						{
							case "AND":
								$query .= implode(
									" " . $where->operator,
									array_map(function ($value) use (&$where, &$types, &$values)
									{
										$types .= $where->type;
										$values[] = $value;
										return sprintf(" `%s`.`%s` = ?", $where->table, $where->field);
									}, $where->values)
								);
								break;
							case "OR":
								$query .= " (" . implode(
										" " . $where->operator . " ",
										array_map(function ($value) use (&$where, &$types, &$values)
										{
											$types .= $where->type;
											$values[] = $value;
											return sprintf("`%s`.`%s` = ?", $where->table, $where->field);
										}, $where->values)
									) . ")";
								break;
							case "IN":
								$query .= sprintf(" `%s`.`%s` IN (", $where->table, $where->field) .
									implode(", ",
										array_map(function ($value) use (&$where, &$types, &$values)
										{
											$types .= $where->type;
											$values[] = $value;
											return "?";
										}, $where->values)
									) . ")";
								break;
							default:
								trigger_error("This should not be able to be reached.", E_USER_ERROR);
						}
					}
					else
					{
						$query .= sprintf(" `%s`.`%s` = ?", $where->table, $where->field);
						$types .= $where->type;
						$values[] = $where->value;
					}
				}
				else
				{
					if (!empty($where->values))
					{
						switch ($where->operator)
						{
							case "AND":
								$query .= implode(
									" " . $where->operator . " ",
									array_map(function ($value) use (&$where)
									{
										return sprintf(
											(is_numeric($value) ? "`%s`.`%s` = %d" : "`%s`.`%s` = '%s'"),
											$where->table, $where->field, $value
										);
									}, $where->values)
								);
								break;
							case "OR":
								$query .= " (" . implode(
										" " . $where->operator . " ",
										array_map(function ($value) use (&$where)
										{
											return sprintf(
												(is_numeric($value) ? "`%s`.`%s` = %d" : "`%s`.`%s` = '%s'"),
												$where->table, $where->field, $value
											);
										}, $where->values)
									) . ")";
								break;
							case "IN":
								$query .= "%s`.`%s` IN (" . implode(", ", $where->values) . ")";
								break;
							default:
								trigger_error("This should not be able to be reached.", E_USER_ERROR);
						}
					}
					else
					{
						if (is_numeric($where->value))
						{
							$query .= sprintf(" `%s`.`%s` = %d", $where->table, $where->field, $where->value);
						}
						else
						{
							$query .= sprintf(" `%s`.`%s` = '%s'", $where->table, $where->field, $where->value);
						}
					}
				}
			}
		}

		if ($printQuery === true)
		{
			return array(
				"query" => $query,
				"types" => $types,
				"values" => $values
			);
		}

		$statement = Database::prepare($query, $types);
		return call_user_func_array(array($statement, "execute"), $values);
	}

	/**
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

		$join->how = strtoupper($join->how);
		$join->source = $this->table;

		if (($join->how === "USING") && (empty($join->field)))
		{
			throw new InvalidArgumentException("Missing field for JOIN USING.");
		}
		elseif (($join->how === "ON") && (empty($join->from) || empty($join->to)))
		{
			throw new InvalidArgumentException("Missing from / to for JOIN ON.");
		}

		$this->joins[] = $join;
	}

	/**
	 * @param array $where
	 * @throws InvalidArgumentException
	 */
	public function where(array $where)
	{
		$where = (object)$where;
		if (!empty($where->operator))
		{
			$where->operator = strtoupper($where->operator);
		}

		if (empty($where->field) || (empty($where->value) && empty($where->values)))
		{
			throw new InvalidArgumentException("Missing field / value.");
		}
		elseif (!empty($where->value) && !empty($where->values))
		{
			throw new InvalidArgumentException("You cannot have multiple value & values.");
		}
		elseif (!empty($where->values) && empty($where->operator))
		{
			throw new InvalidArgumentException("Missing operator (AND|OR|IN) with values.");
		}
		elseif (!empty($where->operator) && !in_array($where->operator, array("AND", "OR", "IN")))
		{
			throw new InvalidArgumentException("Invalid operator for where statement.");
		}

		if (empty($where->operator))
		{
			$where->operator = "AND";
		}

		$where->table = empty($where->table) ? $this->table : $where->table;
		$this->where[] = $where;
	}
}