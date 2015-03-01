<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Application extends Model
{
	/**
	 * Get the relevant Application by it's ID.
	 *
	 * @param int $id
	 * @return Model_Application
	 */
	public static function getById($id)
	{
		/** @var Model_Application $application */
		$application = parent::getById($id);
		if (empty($application))
		{
			$application = Database::prepare(
				"SELECT
					`application_id` AS 'id',
					`key`, `secret`, `name`,
					`created`, `updated`, `status`
				 FROM `Application`
				 WHERE `application_id` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($application);
		}
		return $application;
	}

	/**
	 * Get the relevant Application by it's key.
	 *
	 * @param string $key
	 * @return Model_Application
	 */
	public static function getByKey($key)
	{
		$cacheKey = static::cacheName() . ".key." . $key;
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare("SELECT `application_id` FROM `Application` WHERE `key` = ? AND `status` = 1", "s")
				->execute($key)->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return !empty($id) ? static::getById($id) : null;
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $key;
	/**
	 * @var string
	 */
	protected $secret;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $created;
	/**
	 * @var string
	 */
	protected $updated;
	/**
	 * @var int
	 */
	protected $status;

	/**
	 * @param string $name
	 * @param string $contact_email
	 */
	public function __construct($name = null, $contact_email = null)
	{
		if ($this->getId() === null)
		{
			if (empty($name))
			{
				trigger_error("Missing NAME passed to the APPLICATION constructor", E_USER_ERROR);
			}
			$this->name = $name;

			if (empty($contact_email))
			{
				trigger_error("Missing CONTACT_EMAIL passed to the APPLICATION constructor", E_USER_ERROR);
			}
			$this->metadata->contact_email = $contact_email;
		}
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getContactEmail()
	{
		return $this->metadata->contact_email;
	}

	/**
	 * @return string
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * Render the application.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"key" => $this->key,
				"name" => $this->name,
				"contact_email" => $this->getContactEmail(),
				"created" => $this->created,
				"updated" => $this->updated
			)
		));
	}

	/**
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		if (empty($this->id))
		{
			$query = "INSERT INTO `Application` (`key`, `secret`, `name`, `created`) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
			$trying = true;
			while ($trying)
			{
				try
				{
					$key = md5(uniqid());
					$secret = md5("secret-", uniqid());
					/** @var _Database_State $result */
					$result = Database::prepare($query, "sss")->execute($key, $secret, $this->name);
					$trying = false;
					$this->id = $result->insert_id;
					$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
				}
				catch (DatabaseException $e)
				{
					Log::error((string)$e);
				}
			}
		}
		else
		{
			Database::prepare(
				"UPDATE `Application` SET `name` = ? WHERE `application_id` = ?",
				"si"
			)->execute(
				$this->name,
				$this->id
			);
			$this->updated = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}
}