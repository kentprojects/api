<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Metadata implements ArrayAccess, JsonSerializable
{
	protected $data = array();
	protected $root;

	public function __construct($root = null)
	{
		if ($root == null)
		{
			return;
		}

		$this->root = $root;

		$statement = Database::prepare("SELECT `key`, `value` FROM `Metadata` WHERE `root` = ?", "s");
		$results = $statement->execute($root)->all();

		foreach ($results as $result)
		{
			$this->offsetSet($result->key, $result->value);
		}
	}

	public function __get($key)
	{
		return $this->offsetExists($key)
			? current($this->data[$key])
			: null;
	}

	public function __set($key, $value)
	{
		$this->data[$key] = array($value);
	}

	/**
	 * @return array|stdClass
	 */
	function jsonSerialize()
	{
		$data = array();
		foreach ($this->data as $key => $value)
		{
			if (is_array($value) && (count($value) === 1))
			{
				$data[$key] = array_shift($value);
			}
			else
			{
				$data[$key] = $value;
			}
		}

		if (empty($data))
		{
			$data = new stdClass;
		}

		return $data;
	}

	public function offsetCount($key)
	{
		return count($this->data[$key]);
	}

	public function offsetExists($key)
	{
		return isset($this->data[$key]);
	}

	public function offsetGet($key)
	{
		return $this->data[$key];
	}

	public function offsetSet($key, $value)
	{
		if (!$this->offsetExists($key))
		{
			$this->data[$key] = array($value);

			return;
		}

		if (array_search($value, $this->data[$key]) !== false)
		{
			return;
		}

		$this->data[$key][] = $value;
	}

	public function offsetUnset($key)
	{
		if ($this->offsetExists($key))
		{
			unset($this->data[$key]);
		}
	}

	public function save($root = null)
	{
		if (!empty($root))
		{
			$this->root = $root;
		}

		if (empty($this->root) || empty($this->data))
		{
			return;
		}

		Database::prepare("DELETE FROM `Metadata` WHERE `root` = ?", "s")->execute($this->root);

		foreach ($this->data as $key => $values)
		{
			foreach ($values as $value)
			{
				Database::prepare("INSERT INTO Metadata (`root`, `key`, `value`) VALUES (?, ?, ?)", "sss")
					->execute($this->root, $key, $value);
			}
		}
	}
}