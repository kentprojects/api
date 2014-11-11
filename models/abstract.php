<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Model_Abstract implements JsonSerializable
{
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
	 * Save the Model.
	 *
	 * @return void
	 */
	public function save()
	{
		$this->metadata->save(($this->getId() !== null) ? $this->getClassName() : null);
	}
}