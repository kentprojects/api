<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Metadata implements ArrayAccess, Countable
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

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->offsetExists($key)
			? current($this->data[$key])
			: null;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->offsetExists($key);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->data[$key] = array($value);
	}

	public function count()
	{
		return count($this->data);
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
		return $this->offsetExists($key)
			? $this->data[$key]
			: array();
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

	/**
	 * @return array|stdClass
	 */
	function render()
	{
		$data = new stdClass;
		if (count($this->data))
		{
			return $data;
		}

		foreach ($this->data as $key => $value)
		{
			if (is_array($value) && (count($value) === 1))
			{
				$data->$key = array_shift($value);
			}
			else
			{
				$data->$key = $value;
			}
		}
		return $data;
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

		ksort($this->data);

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