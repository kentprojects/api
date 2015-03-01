<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ModelMap
 * This class is designed to bring two objects together by way of a map table.
 */
abstract class ModelMap implements Countable, IteratorAggregate, JsonSerializable
{
	protected $data = array();
	protected $foreignClass;
	protected $source;

	protected $clearSQL;
	protected $getSQL;
	protected $saveSQL;

	/**
	 * @param Model $sourceObject
	 * @param string $foreignClass
	 * @param string $getSQL
	 * @param string $clearSQL
	 * @param string $saveSQL
	 */
	public function __construct(Model $sourceObject, $foreignClass, $getSQL = null, $clearSQL = null, $saveSQL = null)
	{
		$this->source = $sourceObject;
		$this->foreignClass = $foreignClass;

		if (!empty($getSQL))
		{
			$this->getSQL = $getSQL;
		}

		if (!empty($clearSQL))
		{
			$this->clearSQL = $clearSQL;
		}

		if (!empty($saveSQL))
		{
			$this->saveSQL = $saveSQL;
		}

		if ($this->source->getId() !== null)
		{
			$this->fetch();
		}
	}

	/**
	 * @param Model $model
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function add(Model $model)
	{
		if (get_class($model) != $this->foreignClass)
		{
			throw new InvalidArgumentException("Model of " . get_class($model) . " is not of " . $this->foreignClass);
		}

		if (array_key_exists($model->getId(), $this->data))
		{
			return;
		}

		$this->data[$model->getId()] = $model;
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
	protected function fetch()
	{
		if (empty($this->getSQL))
		{
			throw new InvalidArgumentException("Missing getSQL for " . get_called_class());
		}

		$statement = Database::prepare($this->getSQL, "i");
		$results = $statement->execute($this->source->getId());
		if (count($results) == 0)
		{
			return;
		}

		/** @var Model $class */
		$class = $this->foreignClass;
		$this->data = array();
		foreach ($results->singlevals() as $foreignId)
		{
			$this->data[$foreignId] = $class::getById($foreignId);
		}
	}

	/**
	 * @param int $modelId
	 * @return Model
	 */
	public function get($modelId)
	{
		return array_key_exists($modelId, $this->data) ? $this->data[$modelId] : null;
	}

	/**
	 * Useful so the ModelMap can partake in a foreach loop.
	 *
	 * @return array
	 */
	public function getIterator()
	{
		return array_values($this->data);
	}

	/**
	 * Useful so the ModelMap can partake in json_encode.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array_values($this->data);
	}

	/**
	 * @param Model $model
	 * @return void
	 */
	public function remove(Model $model)
	{
		if (array_key_exists($model->getId(), $this->data))
		{
			unset($this->data[$model->getId()]);
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
		if (empty($this->clearSQL))
		{
			throw new InvalidArgumentException("Missing clearSQL for " . get_called_class());
		}

		$sourceId = $this->source->getId();
		Database::prepare($this->clearSQL, "i")->execute($sourceId);

		if (empty($this->data))
		{
			return;
		}

		if (!empty($this->saveSQL))
		{
			$query = array();
			$types = "";
			$values = array();

			foreach ($this->data as $id => $model)
			{
				$query[] = "(?,?)";
				$types .= "ii";
				array_push($values, $sourceId, $id);
			}

			$this->clear();
			$statement = Database::prepare(str_replace("(?,?)", implode(", ", $query), $this->saveSQL), $types);
			call_user_func_array(array($statement, "execute"), $values);
		}
	}
}