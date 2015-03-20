<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Timing
{
	/**
	 * @var Timer
	 */
	protected static $curr;
	/**
	 * @var Timer
	 */
	protected static $root;

	/**
	 * @param $name
	 * @return void
	 */
	public static function start($name)
	{
		if (isset(static::$curr))
		{
			static::$curr = new Timer(static::$curr, $name);
		}
		else
		{
			static::$curr = static::$root = new Timer(null, $name);
		}
	}

	/**
	 * @param $name
	 * @return void
	 */
	public static function stop($name)
	{
		$node = static::$curr;
		while ($node->name !== $name)
		{
			if ($node === static::$root)
			{
				return;
			}
			$node = $node->parent;
		}
		$node->stop = microtime(true);
		static::$curr = $node->parent;
	}

	/**
	 * @param bool $asObject
	 * @return string|stdClass
	 */
	public static function export($asObject = false)
	{
		self::$root->stop = microtime(true);
		$output = json_encode(self::$root, JSON_PRETTY_PRINT);
		return $asObject ? json_decode($output) : $output;
	}
}
class Timer extends Timing implements JsonSerializable
{
	public $start = null;
	public $stop = null;
	public $name = null;
	/**
	 * @var Timer
	 */
	public $parent = null;
	public $children = array();

	public function __construct($parent, $name)
	{
		$this->start = microtime(true);
		$this->name = $name;

		if ($parent !== null)
		{
			$this->parent = $parent;
			$parent->children [$name] =& $this;
		}
	}

	public function jsonSerialize()
	{
		if (count($this->children) > 0)
		{
			return array(
				'offset' => $this->format($this->getOffset()),
				'length' => $this->format($this->getStop() - $this->start),
				'children' => $this->children
			);
		}
		else
		{
			return $this->format($this->getStop() - $this->start) . ' +' . $this->format($this->getOffset());
		}
	}

	protected function getOffset()
	{
		return $this->start - static::$root->start;
	}

	protected function getStop()
	{
		if (isset($this->stop))
		{
			return $this->stop;
		}
		else
		{
			return $this->parent->getStop();
		}
	}

	protected function format($i)
	{
		return sprintf('%.1fms', $i * 1000);
	}
}