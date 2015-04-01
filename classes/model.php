<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Model
 * A model is any class that interacts directly with the database.
 */
abstract class Model
{
	/**
	 * An array of the allowed fields.
	 * @var array
	 */
	private static $limitFields = array();

	/**
	 * Return the standard Cache name for this model.
	 * @return string
	 */
	protected static function cacheName()
	{
		return Cache::key("model") . strtolower(str_replace("Model_", "", get_called_class()));
	}

	/**
	 * Return the standard class name for this model.
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
	 * @return mixed|null
	 */
	public static function getById($id)
	{
		return Cache::get(static::cacheName() . "." . $id);
	}

	/**
	 * Set specific fields for this model to return.
	 *
	 * @param array $fields
	 * @return void
	 */
	public static function returnFields(array $fields)
	{
		self::$limitFields[get_called_class()] = array_merge(array("id"), $fields);
	}

	/**
	 * The model's metadata.
	 * @var Metadata
	 */
	protected $metadata;

	/**
	 * Build a new Model.
	 * Always remember to call this, otherwise you'll lose access to the precious Metadata class.
	 */
	public function __construct()
	{
		$this->metadata = new Metadata(($this->getId() !== null) ? $this->getClassName() : null);
	}

	/**
	 * Clear the caches for this entity.
	 * @return void
	 */
	public final function clearCaches()
	{
		call_user_func_array(array("Cache", "delete"), $this->clearCacheStrings());
	}

	/**
	 * Return a list of keys to send to Cache::delete when clearCaches() is called.
	 * @return array
	 */
	public function clearCacheStrings()
	{
		return array(
			$this->getCacheName()
		);
	}

	/**
	 * Get the Cache name for the current model, optionally appending some more text.
	 *
	 * @param string $append
	 * @return string
	 */
	public function getCacheName($append = null)
	{
		return static::cacheName() . "." . $this->getId() . (!empty($append) ? "." . $append : "");
	}

	/**
	 * Get the class name for the current model.
	 * @return string
	 */
	public function getClassName()
	{
		return static::className() . "/" . $this->getId();
	}

	/**
	 * Get the entity name for the current model.
	 * @return string
	 */
	public function getEntityName()
	{
		return strtolower(str_replace("Model_", "", get_called_class()) . "/" . $this->getId());
	}

	/**
	 * Get the ID of a Model.
	 * @return int|string
	 */
	public abstract function getId();

	/**
	 * Render the model.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return array(
			"id" => $this->getId()
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
	 * @return void
	 */
	public function save()
	{
		$this->metadata->save(($this->getId() !== null) ? $this->getClassName() : null);
		$this->clearCaches();
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