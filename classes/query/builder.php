<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Query_Builder
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
	 * @return _Database_Result|_Database_State
	 */
	public function execute()
	{
		$query = "SELECT " . implode(",", $this->fields) . " FROM `" . $this->table . "`";
		$types = "";
		$values = array();

		if (!empty($this->joins))
		{
			foreach ($this->joins as $join)
			{
				$joinQuery = sprintf(" JOIN `%s`", $join->table);
				if ($join->how === "using")
				{
					$joinQuery .= sprintf(" USING (`%s`)", $join->field);
				}
				elseif ($join->how === "on")
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
			foreach ($this->where as $where)
			{
				if (!empty($where->type))
				{
					$query .= sprintf(" `%s`.`%s` = ?", $where->table, $where->field);
					$types .= $where->type;
					$values[] = $where->value;
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

		$join->how = strtolower($join->how);
		$join->source = $this->table;

		if (($join->how === "using") && (empty($join->field)))
		{
			throw new InvalidArgumentException("Missing field for JOIN USING.");
		}
		elseif (($join->how === "on") && (empty($join->from) || empty($join->to)))
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

		if (empty($where->field) || empty($where->value))
		{
			throw new InvalidArgumentException("Missing field / value.");
		}

		$where->table = empty($where->table) ? $this->table : $where->table;
		$this->where[] = $where;
	}
}