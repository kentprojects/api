<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Abstract implements JsonSerializable
{
	/**
	 * An array of the allowed fields.
	 *
	 * @var array
	 */
	private static $limitFields = array();

	/**
	 * @return string
	 */
	private static function cachename()
	{
		return Cache::PREFIX . ".model." . strtolower(str_replace("/", ".", static::classname()));
	}
	
	/**
	 * @return string
	 */
	private static function classname()
	{
		return str_replace("_", "/", get_called_class());
	}
	
	/**
	 * Get the relevant Model by it's ID.
	 *
	 * @return mixed|null
	 */
	public static function getById($id)
	{
		return Cache::get(static::cachename().".".$id);
	}

	/**
	 * @param array $fields
	 * @return void
	 */
	public static function returnFields(array $fields)
	{
		static::$limitFields[get_called_class()] = array_merge(array("id"), $fields);
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
		return static::cachename().".".$this->getId();
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return static::classname()."/".$this->getId();
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
	 * Validate that these fields are allowed back by the API.
	 *
	 * @param array $jsonSerialized
	 * @return array
	 */
	protected function validateFields(array $jsonSerialized)
	{
		if (!empty(static::$limitFields[get_called_class()]))
		{
			foreach ($jsonSerialized as $key => $value)
			{
				if (!in_array($key, static::$limitFields[get_called_class()]))
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
	}
}