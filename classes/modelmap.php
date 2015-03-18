<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ModelMap
 * This class is designed to bring two objects together by way of a map table.
 */
abstract class ModelMap implements Countable, IteratorAggregate
{
	protected $cacheName;
	protected $data = array();
	protected $foreignClass;
	protected $source;

	protected $clearSQL;
	protected $getSQL;
	protected $saveSQL;

	/**
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
	 * @throws CacheException
	 * @return void
	 */
	public function clearCaches()
	{
		call_user_func_array(array("Cache", "delete"), $this->clearCacheStrings());
	}

	/**
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
		Log::debug($strings);
		return $strings;
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
	 * @param int $modelId
	 * @return Model
	 */
	public function get($modelId)
	{
		return array_key_exists($modelId, $this->data) ? $this->data[$modelId] : null;
	}

	/**
	 * @return array
	 */
	public function getIds()
	{
		return array_keys($this->data);
	}

	/**
	 * Useful so the ModelMap can partake in a foreach loop.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator(array_values($this->data));
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
		return array_values(
			array_map(
				function (Model $model) use ($request, &$response, $acl, $internal)
				{
					return $model->render($request, $response, $acl, $internal);
				},
				$this->data
			)
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