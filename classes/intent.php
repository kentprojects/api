<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Intent implements JsonSerializable
{
	const STATE_OPEN = "intent:state:open";
	const STATE_ACCEPTED = "intent:state:accepted";
	const STATE_REJECTED = "intent:state:rejected";

	/**
	 * @param int $id
	 * @return Intent
	 */
	public static function getById($id)
	{
		$model = Model_Intent::getById($id);
		if (empty($model))
		{
			return null;
		}

		/** @var Intent $class */
		$class = static::getHandlerClassName($model->getHandler());

		return new $class($model);
	}

	/**
	 * @param string $handler
	 * @return string
	 */
	public static function getHandlerClassName($handler)
	{
		$className = "Intent_";
		$className .= implode(
			"_", array_map(
				function ($h)
				{
					return ucfirst($h);
				},
				explode("_", static::formatHandler($handler))
			)
		);
		if (!class_exists($className))
		{
			trigger_error("Class not found: $className", E_USER_ERROR);
		}

		return $className;
	}

	/**
	 * @param string $handler
	 * @return string
	 */
	public static function formatHandler($handler)
	{
		return strtolower(
			str_replace("-", "_", $handler)
		);
	}

	/**
	 * @var Metadata
	 */
	protected $data;
	/**
	 * @var Model_Intent
	 */
	protected $model;

	public function __construct(Model_Intent $model)
	{
		$this->model = $model;

		if ($this->model->getHandler() !== $this->getHandlerName())
		{
			throw new InvalidArgumentException("This model's handler is different to this handler.");
		}

		$this->data = new Metadata(($this->model->getId() !== null) ? $this->model->getClassName() : null);
	}

	/**
	 * Run some pre-requisite stuff.
	 *
	 * @param array $data
	 * @throws IntentException
	 * @return void
	 */
	public function create(array $data)
	{
		if ($this->model->getId() !== null)
		{
			throw new IntentException("You can't create a new intent with an existing intent model.");
		}
	}

	/**
	 * @param array $data
	 * @throws Exception
	 * @return void
	 */
	public function delete(array $data)
	{
		throw new Exception("Why are you calling a delete method of an Intent?");
	}

	/**
	 * @return string
	 */
	protected final function getHandlerName()
	{
		return strtolower(str_replace("Intent_", "", get_called_class()));
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array(
			"id" => $this->model->getId(),
			"user" => $this->model->getUser(),
			"handler" => $this->getHandlerName(),
			"data" => $this->data->jsonSerialize(),
			"state" => $this->model->getState()
		);
	}

	/**
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @return void
	 */
	protected function mergeData(array $data)
	{
		if (!empty($data[0]))
		{
			throw new InvalidArgumentException("Only associative arrays can be passed to Intent::mergeData.");
		}

		foreach ($data as $key => $value)
		{
			if (is_array($value) || is_object($value))
			{
				throw new InvalidArgumentException("Only key->value pairs can be passed to Intent::mergeData.");
			}
			elseif ($value === null)
			{
				unset($this->data[$key]);
			}
			else
			{
				$this->data->$key = $value;
			}
		}
	}

	/**
	 * @return void
	 */
	public function save()
	{
		$this->model->save();
		$this->data->save(($this->model->getId() !== null) ? $this->model->getClassName() : null);
	}

	/**
	 * @param string $state
	 * @return void
	 */
	public function state($state)
	{
		switch ($state)
		{
			case static::STATE_OPEN:
			case static::STATE_ACCEPTED:
			case static::STATE_REJECTED:
				$this->model->setState($state);
				break;
			default:
				throw new InvalidArgumentException("This state should be a valid Intent STATE constant.");
		}
	}

	/**
	 * @param array $data
	 * @throws IntentException
	 * @return void
	 */
	public function update(array $data)
	{
		if ($this->model->getId() === null)
		{
			throw new IntentException("You can't update an intent without an existing intent model.");
		}
	}
}