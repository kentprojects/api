<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Metadata
 * This class allows any class to store any form of non-indexed data in the database.
 */
final class Metadata implements ArrayAccess, Countable
{
	/**
	 * The data being stored.
	 * @var array
	 */
	protected $data = array();
	/**
	 * The root by which the data above is stored by.
	 * @var string
	 */
	protected $root;

	/**
	 * Build a new Metadata object.
	 *
	 * @param string $root
	 */
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
	 * Get a single value from the metadata.
	 *
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
	 * Do we even have a single value from the metadata.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->offsetExists($key);
	}

	/**
	 * Set an individual value to the metadata, overwriting any existing data if necessary.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->data[$key] = array($value);
	}

	/**
	 * Count the number of metadata objects.
	 * @return int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Count the number of values listed under one key.
	 *
	 * @param string $key
	 * @return int
	 */
	public function offsetCount($key)
	{
		return count($this->data[$key]);
	}

	/**
	 * Do we even have a value listed under the metadata with that key?
	 *
	 * @param string $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->data[$key]);
	}

	/**
	 * Get the values listed under a specific key.
	 *
	 * @param string $key
	 * @return array
	 */
	public function offsetGet($key)
	{
		return $this->offsetExists($key)
			? $this->data[$key]
			: array();
	}

	/**
	 * Set an individual value to the metadata, appending to any existing data if necessary.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
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

	/**
	 * Delete an item from the metadata.
	 *
	 * @param string $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		if ($this->offsetExists($key))
		{
			unset($this->data[$key]);
		}
	}

	/**
	 * Render the complete list of the metadata object.
	 * @return array|stdClass
	 */
	function render()
	{
		$data = new stdClass;

		if (count($this->data) > 0)
		{
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
		}

		return $data;
	}

	/**
	 * Save the metadata information.
	 *
	 * @param null $root
	 * @return void
	 */
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