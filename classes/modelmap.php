<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ModelMap
 * This class is designed to bring two objects together by way of a map table.
 * It's based off one model connected to many other models of a certain type.
 */
abstract class ModelMap implements Countable, IteratorAggregate
{
	/**
	 * The cache name that the model map will be cached under.
	 * @var string
	 */
	protected $cacheName;
	/**
	 * The Models connected to this model map.
	 * @var array
	 */
	protected $data = array();
	/**
	 * The class name of the Models within $this->data.
	 * @var string
	 */
	protected $foreignClass;
	/**
	 * The Model acting as the source for this model map.
	 * @var Model
	 */
	protected $source;

	/**
	 * The SQL that will clear the model map.
	 * @var string
	 */
	protected $clearSQL;
	/**
	 * The SQL that will get the data for the model map.
	 * @var string
	 */
	protected $getSQL;
	/**
	 * The SQL that will set the data for the model map.
	 * @var null|string
	 */
	protected $saveSQL;

	/**
	 * Build a new Model map.
	 *
	 * @param Model $sourceObject
	 * @param string $foreignClass
	 * @param string $cacheName
	 * @param string $getSQL
	 * @param string $clearSQL
	 * @param string $saveSQL
	 */
	public function __construct(Model $sourceObject, $foreignClass, $cacheName, $getSQL = null, $clearSQL = null, $saveSQL = null)
	{
		$this->foreignClass = $foreignClass;
		$this->source = $sourceObject;

		$this->cacheName = $this->source->getCacheName($cacheName);

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

		/**
		 * If we have been given a valid source, then go fetch the data for the model map.
		 */
		if ($this->source->getId() !== null)
		{
			$this->fetch();
		}
	}

	/**
	 * Add a new Model to this model map.
	 * This does does not save the model map, as this is something you will need to do yourself.
	 *
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
	 * Clear the model map.
	 * @return void
	 */
	public function clear()
	{
		$this->data = array();
	}

	/**
	 * Clear the caches for each Model in the Model Map.
	 * @return void
	 */
	public function clearCaches()
	{
		call_user_func_array(array("Cache", "delete"), $this->clearCacheStrings());
	}

	/**
	 * Get all the cache strings from the Models in the Model Map.
	 * @return array
	 */
	public function clearCacheStrings()
	{
		$strings = array();
		foreach ($this->data as $model)
		{
			/** @var Model $model */
			$strings = array_merge($strings, $model->clearCacheStrings());
		}
		return $strings;
	}

	/**
	 * Count the number of entries in the Model Map.
	 * @return int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Get the entries for this Model Map.
	 *
	 * @throws InvalidArgumentException
	 * @return void
	 */
	protected function fetch()
	{
		if (empty($this->getSQL))
		{
			throw new InvalidArgumentException("Missing getSQL for " . get_called_class());
		}

		$foreignIds = Cache::get($this->cacheName);
		if (empty($foreignIds))
		{
			$results = Database::prepare($this->getSQL, "i")->execute($this->source->getId());
			if (count($results) == 0)
			{
				return;
			}
			$foreignIds = $results->singlevals();
			Cache::set($this->cacheName, $foreignIds, Cache::HOUR);
		}

		/** @var Model $class */
		$class = $this->foreignClass;
		$this->data = array();
		foreach ($foreignIds as $foreignId)
		{
			$this->data[$foreignId] = $class::getById($foreignId);
		}
	}

	/**
	 * Get a specific Model.
	 *
	 * @param int $modelId
	 * @return Model
	 */
	public function get($modelId)
	{
		return array_key_exists($modelId, $this->data) ? $this->data[$modelId] : null;
	}

	/**
	 * Get a list of the IDs from this Model Map.
	 * @return array
	 */
	public function getIds()
	{
		return array_keys($this->data);
	}

	/**
	 * Useful so the ModelMap can partake in a foreach loop.
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator(array_values($this->data));
	}

	/**
	 * Remove a specific Model from the map.
	 *
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
	 * Render the model map.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return array_map(
			function (Model $model) use ($request, &$response, $acl, $internal)
			{
				return $model->render($request, $response, $acl, $internal);
			},
			array_values($this->data)
		);
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

		Database::prepare($this->clearSQL, "i")->execute($this->source->getId());
		Cache::delete($this->cacheName);

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
				array_push($values, $this->source->getId(), $id);
			}

			$this->clear();
			$statement = Database::prepare(str_replace("(?,?)", implode(", ", $query), $this->saveSQL), $types);
			call_user_func_array(array($statement, "execute"), $values);
		}
	}
}