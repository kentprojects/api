<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model implements JsonSerializable
{
	/**
	 * An array of the allowed fields.
	 *
	 * @var array
	 */
	private static $limitFields = array();

	/**
	 * Builds a new model like the Database does.
	 *
	 * @param array|stdClass $data
	 * @param string $idField
	 * @throws InvalidArgumentException
	 * @return Model
	 */
	public static function build($data, $idField)
	{
		if (config("environment") !== "testing")
		{
			throw new InvalidArgumentException("Only the testing environment can call this!");
		}

		if (is_object($data))
		{
			if (get_class($data) !== "stdClass")
			{
				throw new InvalidArgumentException("Data is an object not of stdClass.");
			}
		}

		$class = get_called_class();

		/** @var Model $object */
		$object = new $class;

		foreach ($data as $key => $value)
		{
			if ($key === $idField)
			{
				$object->id = $value;
			}
			else
			{
				$object->$key = $value;
			}
		}

		$object->__construct();

		return $object;
	}

	/**
	 * @return string
	 */
	protected static function cacheName()
	{
		return Cache::PREFIX . ".model." . (config("environment") === "development" ? "dev." : "") .
		strtolower(str_replace("_", ".", get_called_class()));
	}

	/**
	 * @return string
	 */
	protected static function className()
	{
		return str_replace("_", "/", get_called_class());
	}

	/**
	 * Get the relevant Model by it's ID.
	 *
	 * @param mixed $id
	 * @throws CacheException
	 * @return mixed|null
	 */
	public static function getById($id)
	{
		try
		{
			return Cache::get(static::cacheName() . "." . $id);
		}
		catch (CacheException $e)
		{
			if ($e->getCode() === Memcached::RES_NOTFOUND)
			{
				return null;
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * @param array $fields
	 * @return void
	 */
	public static function returnFields(array $fields)
	{
		self::$limitFields[get_called_class()] = array_merge(array("id"), $fields);
	}

	/**
	 * @var Metadata
	 */
	protected $metadata;

	/**
	 * Build a new Model
	 */
	public function __construct()
	{
		$this->metadata = new Metadata(($this->getId() !== null) ? $this->getClassName() : null);
	}

	/**
	 * @return string
	 */
	public function getCacheName()
	{
		return static::cacheName() . "." . $this->getId();
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return static::className() . "/" . $this->getId();
	}

	/**
	 * Get the ID of a Model.
	 *
	 * @return int|string
	 */
	public abstract function getId();

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array(
			"id" => $this->getId()
		);
	}

	/**
	 * @param string|null $entity
	 * @return array
	 */
	protected function jsonPermissions($entity = null)
	{
		return array(
			"permissions" => KentProjects::ACL(
				strtolower(empty($entity) ? str_replace("Model/", "", $this->getClassName()) : $entity)
			)
		);
	}

	/**
	 * Validate that these fields are allowed back by the API.
	 *
	 * @param array $jsonSerialized
	 * @return array
	 */
	protected function validateFields(array $jsonSerialized)
	{
		if (!empty(self::$limitFields[get_called_class()]))
		{
			foreach ($jsonSerialized as $key => $value)
			{
				if (!in_array($key, self::$limitFields[get_called_class()]))
				{
					unset($jsonSerialized[$key]);
				}
			}
		}

		return $jsonSerialized;
	}

	/**
	 * Save the Model.
	 *
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		$this->metadata->save(($this->getId() !== null) ? $this->getClassName() : null);
		Cache::delete($this->getCacheName());
	}

	/**
	 * This will be a really cheeky function to update models.
	 * Please remember to save after using this function!
	 *
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function update(array $data)
	{
	}
}