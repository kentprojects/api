<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_User extends Model
{
	/**
	 * @var array
	 */
	protected static $roles = array("staff", "student");

	/**
	 * Get a staff by their Email.
	 *
	 * @param string $email
	 * @return Model_User
	 */
	public static function getByEmail($email)
	{
		$cacheKey = static::cacheName() . ".email." . $email;
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare("SELECT `user_id` FROM `User` WHERE `email` = ? AND `status` = 1", "s")
				->execute($email)->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return static::getById($id);
	}

	/**
	 * Get the relevant User by it's ID.
	 *
	 * @param int $id
	 * @return Model_User|null
	 */
	public static function getById($id)
	{
		if (empty($id))
		{
			return null;
		}
		/** @var Model_User $user */
		$user = parent::getById($id);
		if (empty($user))
		{
			$user = Database::prepare(
				"SELECT
					`user_id` AS 'id',
					`uid`,
					`email`,
					`first_name`,
					`last_name`,
					`role`,
					`created`,
					`lastlogin`,
					`updated`,
					`status`
				 FROM `User`
				 WHERE `user_id` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($user);
		}
		return $user;
	}

	public static function getByUid($uid)
	{
		$cacheKey = static::cacheName() . ".uid." . $uid;
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare("SELECT `user_id` FROM `User` WHERE `uid` = ? AND `status` = 1", "s")
				->execute($uid)->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return static::getById($id);
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $uid;
	/**
	 * @var string
	 */
	protected $email;
	/**
	 * @var string
	 */
	protected $first_name;
	/**
	 * @var string
	 */
	protected $last_name;
	/**
	 * @var string "staff"|"student"
	 */
	protected $role;
	/**
	 * @var string
	 */
	protected $created;
	/**
	 * @var string
	 */
	protected $lastlogin;
	/**
	 * @var string
	 */
	protected $updated;
	/**
	 * @var int
	 */
	protected $status;

	/**
	 * @var Model_Group
	 */
	protected $group;
	/**
	 * @var UserYearMap
	 */
	protected $years;

	public function __construct($uid = null, $email = null, $role = null)
	{
		if ($this->getId() === null)
		{
			if (empty($uid))
			{
				trigger_error("Missing UID passed to the USER constructor", E_USER_ERROR);
			}
			$this->uid = $uid;

			if (empty($email))
			{
				trigger_error("Missing EMAIL passed to the USER constructor", E_USER_ERROR);
			}
			$this->email = $email;

			if (empty($role))
			{
				trigger_error("Missing ROLE passed to the USER constructor", E_USER_ERROR);
			}
			$this->role = $role;
		}
		parent::__construct();
	}

	/**
	 * @throws CacheException
	 * @return void
	 */
	public function clearCaches()
	{
		parent::clearCaches();
		Cache::delete($this->getCacheName("group"));
		Cache::delete($this->getCacheName("project"));
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->metadata->description;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * @return Model_Group
	 */
	public function getGroup()
	{
		if (empty($this->group))
		{
			$this->group = Model_Group::getByUser($this);
		}
		return $this->group;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getInterests()
	{
		return $this->metadata["interests"];
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return trim($this->first_name . " " . $this->last_name);
	}

	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}

	public function getYears()
	{
		if (empty($this->years))
		{
			$this->years = new UserYearMap($this);
		}
		return $this->years;
	}

	/**
	 * @return bool
	 */
	public function isStaff()
	{
		return $this->role === "staff";
	}

	/**
	 * @return bool
	 */
	public function isStudent()
	{
		return $this->role === "student";
	}

	/**
	 * Render the user.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		$data = array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"uid" => $this->uid,
				"email" => $this->email,
				"name" => $this->getName(),
				"first_name" => $this->first_name,
				"last_name" => $this->last_name,
				"role" => $this->role
			)
		);

		if (!$internal)
		{
			$this->getYears();
			$data = array_merge($data, array(
				"years" => $this->years->render($request, $response, $acl, true)
			));
		}

		if (!empty($this->group))
		{
			$data = array_merge($data, array(
				"group" => $this->group->render($request, $response, $acl, true)
			));
		}

		$data = array_merge($data, array(
			"bio" => $this->getDescription(),
			"interests" => $this->getInterests(),
			"permissions" => $acl->get($this->getEntityName()),
			"created" => $this->created,
			"last_login" => $this->lastlogin,
			"updated" => $this->updated
		));

		return $this->validateFields($data);
	}

	/**
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function save()
	{
		if ($this->getId() === null)
		{
			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `User` (`uid`, `email`, `first_name`, `last_name`, `role`, `created`)
				 VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)", "sssss"
			)->execute(
				$this->uid, $this->email, $this->first_name, $this->last_name, $this->role
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			Database::prepare(
				"UPDATE `User` SET `email` = ?, `first_name` = ?, `last_name` = ? WHERE `user_id` = ?", "sssi"
			)->execute(
				$this->email, $this->first_name, $this->last_name, $this->id
			);
			$this->updated = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}

	/**
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description)
	{
		$this->metadata->description = strip_tags($description);
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function setFirstName($firstName)
	{
		$this->first_name = $firstName;
	}

	public function setInterests(array $interests)
	{
		unset($this->metadata["interests"]);
		if (!empty($interests))
		{
			foreach ($interests as $interest)
			{
				$this->metadata["interests"] = $interest;
			}
		}
	}

	public function setLastName($lastName)
	{
		$this->last_name = $lastName;
	}

	public function setRole($role)
	{
		$role = strtolower(trim($role));
		if (!in_array($role, static::$roles))
		{
			throw new InvalidArgumentException("Invalid user role '$role'");
		}
		$this->role = $role;
	}

	/**
	 * @param array $data
	 * @throws HttpStatusException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function update(array $data)
	{
		if (!empty($data["bio"]))
		{
			$this->setDescription($data["bio"]);
		}
		if (!empty($data["email"]))
		{
			$this->setEmail($data["email"]);
		}
		if (!empty($data["first_name"]))
		{
			$this->setFirstName($data["first_name"]);
		}
		if (isset($data["interests"]))
		{
			if (is_array($data["interests"]))
			{
				$this->setInterests($data["interests"]);
			}
			else
			{
				throw new HttpStatusException(400, "User interests is not an array.");
			}
		}
		if (!empty($data["last_name"]))
		{
			$this->setLastName($data["last_name"]);
		}
	}
}
