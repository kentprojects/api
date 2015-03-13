<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Notification extends Model
{
	/**
	 * If you add any new types, please update the docs!
	 *
	 * @var array
	 */
	protected static $typeStrings = array(
		"user_got_a_notification" => array(
			"default" => "You just got a notification. Yippee!"
		),
		"user_wants_to_access_a_year" => array(
			"default" => "ACTOR_NAME would like access to YEAR."
		),
		"user_approved_access_to_year" => array(
			"default" => "USER_NAME was granted access to YEAR.",
			"actor" => "You granted USER_NAME access to YEAR.",
			"user" => "ACTOR_NAME granted you access to YEAR."
		),
		"user_rejected_access_to_year" => array(
			"default" => "USER_NAME was granted access to YEAR.",
			"actor" => "You granted USER_NAME access to YEAR.",
			"user" => "ACTOR_NAME granted you access to YEAR."
		),
		"user_wants_to_join_a_group" => array(
			"default" => "ACTOR_NAME would like to join GROUP_NAME.",
			"group_member" => "ACTOR_NAME would like to join your group."
		),
		"user_approved_another_to_join_a_group" => array(
			"default" => "ACTOR_NAME approved USER_NAME to join GROUP_NAME.",
			"group_member" => "ACTOR_NAME approved USER_NAME to join your group."
		),
		"user_rejected_another_to_join_a_group" => array(
			"default" => "ACTOR_NAME rejected USER_NAME to join GROUP_NAME.",
			"group_member" => "ACTOR_NAME rejected USER_NAME to join your group."
		),
		"user_joined_a_group" => array(
			"default" => "ACTOR_NAME joined GROUP_NAME.",
			"group_member" => "ACTOR_NAME joined your group."
		),
		"user_left_a_group" => array(
			"default" => "ACTOR_NAME left GROUP_NAME.",
			"group_member" => "ACTOR_NAME left your group."
		),
		"group_wants_to_undertake_a_project" => array(
			"default" => "GROUP_NAME would like to do PROJECT_NAME."
		),
		"group_undertaken_project_approved" => array(
			"default" => "GROUP_NAME has been approved to do PROJECT_NAME.",
			"group_member" => "Your group has been approved to do PROJECT_NAME.",
			"supervisor" => "You approved GROUP_NAME to do PROJECT_NAME."
		),
		"group_undertaken_project_rejected" => array(
			"default" => "GROUP_NAME has been rejected to do PROJECT_NAME.",
			"group_member" => "Your group has been rejected to do PROJECT_NAME.",
			"supervisor" => "You rejected GROUP_NAME to do PROJECT_NAME."
		),
		"group_released_project" => array(
			"default" => "GROUP_NAME is no longer doing PROJECT_NAME.",
			"group_member" => "Your group is no longer doing PROJECT_NAME."
		)
	);

	/**
	 * Get the relevant Notification by it's ID.
	 *
	 * @param int $id
	 * @return Model_Notification
	 */
	public static function getById($id)
	{
		if (empty($id))
		{
			return null;
		}
		/** @var Model_Notification $notification */
		$notification = parent::getById($id);
		if (empty($notification))
		{
			$notification = Database::prepare(
				"SELECT
					`notification_id` AS 'id',
					`type`,
					`actor_id` AS 'actor',
					`group_id` AS 'group',
					`project_id` AS 'project',
					`user_id` AS 'user',
					`year`,
					`created`
				 FROM `Notification`
				 WHERE `notification_id` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($notification);
		}
		return $notification;
	}

	/**
	 * @return array
	 */
	public static function getNotificationStrings()
	{
		return static::$typeStrings;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public static function isValidType($type)
	{
		return array_key_exists($type, static::$typeStrings);
	}

	/**
	 * @param Model_User $user
	 * @param array $ids
	 * @return void
	 */
	public static function markAsRead(Model_User $user, array $ids)
	{
		if ($user->getId() === null)
		{
			return;
		}

		if (empty($ids))
		{
			return;
		}

		/**
		 * TODO: FINISH THIS.
		 */
	}

	/**
	 * @param Model_Notification $notification
	 * @param array $userIds
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public static function addTargets(Model_Notification $notification, array $userIds)
	{
		if ($notification->getId() === null)
		{
			throw new InvalidArgumentException("Missing notification ID.");
		}
		if (empty($userIds))
		{
			throw new InvalidArgumentException("Missing user IDs.");
		}

		$caches = array();
		$query = array("INSERT INTO", "`User_Notification_Map`", "(`notification_id`, `user_id`)", "VALUES");
		$types = "";
		$values = array();

		foreach ($userIds as $userId)
		{
			$query[] = "(?, ?)";
			$types .= "ii";
			array_push($values, $notification->getId(), $userId);
			$caches[] = Model_User::cacheName() . "." . $userId . ".notifications";
		}

		$statement = Database::prepare(implode(" ", $query), $types);
		call_user_func_array(array($statement, "execute"), $values);

		call_user_func_array(array("Cache", "delete"), $caches);
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $type;
	/**
	 * @var Model_User
	 */
	protected $actor;
	/**
	 * @var Model_Group
	 */
	protected $group;
	/**
	 * @var Intent
	 */
	protected $intent;
	/**
	 * @var Model_Project
	 */
	protected $project;
	/**
	 * @var Model_User
	 */
	protected $user;
	/**
	 * @var Model_Year
	 */
	protected $year;
	/**
	 * @var string
	 */
	protected $created;

	/**
	 * @var string
	 */
	protected $read;

	/**
	 * @param string $type
	 * @param Model_User $actor
	 */
	public function __construct($type = null, Model_User $actor = null)
	{
		if ($this->getId() === null)
		{
			if (empty($type))
			{
				trigger_error("Missing TYPE passed to the NOTIFICATION constructor", E_USER_ERROR);
			}
			elseif (!static::isValidType($type))
			{
				trigger_error("Unknown TYPE '{$type}' passed to the NOTIFICATION constructor", E_USER_ERROR);
			}
			$this->type = $type;

			if (empty($actor))
			{
				trigger_error("Missing ACTOR passed to the NOTIFICATION constructor", E_USER_ERROR);
			}
			$this->actor = $actor;
		}
		parent::__construct();
	}

	/**
	 * @return array
	 */
	public function clearCacheStrings()
	{
		return array_merge(
			parent::clearCacheStrings(),
			array(
				$this->getCacheName("string")
			)
		);
	}

	/**
	 * @return Model_User
	 */
	public function getActor()
	{
		if (!empty($this->actor) && is_numeric($this->actor))
		{
			/** @noinspection PhpParamsInspection */
			$this->actor = Model_User::getById($this->actor);
		}
		return $this->actor;
	}

	/**
	 * @return string
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return Model_Group
	 */
	public function getGroup()
	{
		if (is_numeric($this->group))
		{
			/** @noinspection PhpParamsInspection */
			$this->group = Model_Group::getById($this->group);
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
	 * @return Intent
	 */
	public function getIntent()
	{
		if (is_numeric($this->intent))
		{
			/** @noinspection PhpParamsInspection */
			$this->intent = Intent::getById($this->intent);
		}
		return $this->intent;
	}

	/**
	 * @return Model_Project
	 */
	public function getProject()
	{
		if (is_numeric($this->project))
		{
			/** @noinspection PhpParamsInspection */
			$this->project = Model_Project::getById($this->project);
		}
		return $this->project;
	}

	/**
	 * @return string
	 */
	public function getRead()
	{
		return $this->read;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return Model_User
	 */
	public function getUser()
	{
		if (is_numeric($this->user))
		{
			/** @noinspection PhpParamsInspection */
			$this->user = Model_User::getById($this->user);
		}
		return $this->user;
	}

	/**
	 * @return Model_Year
	 */
	public function getYear()
	{
		if (is_numeric($this->year))
		{
			/** @noinspection PhpParamsInspection */
			$this->year = Model_Year::getById($this->year);
		}
		return $this->year;
	}

	/**
	 * @return bool
	 */
	public function hasGroup()
	{
		return !empty($this->group);
	}

	/**
	 * @return bool
	 */
	public function hasProject()
	{
		return !empty($this->project);
	}

	/**
	 * @return bool
	 */
	public function hasUser()
	{
		return !empty($this->user);
	}

	/**
	 * @return bool
	 */
	public function isUnread()
	{
		return empty($this->read);
	}

	/**
	 * Render the group.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		$this->getActor();
		$this->getGroup();
		$this->getIntent();
		$this->getProject();
		$this->getUser();

		$string = Cache::get($this->getCacheName("string"));
		if (empty($string))
		{
			$string = $this->renderNotificationString($acl->getUser());
			!empty($string) && Cache::set($this->getCacheName("string"), $string, Cache::DAY);
		}

		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"type" => $this->type,
				"text" => $string,
				"actor" => $this->actor->render($request, $response, $acl, true),
				"group" => !empty($this->group) ? $this->group->render($request, $response, $acl, true) : null,
				"intent" => !empty($this->intent) ? $this->intent->render($request, $response, $acl, true) : null,
				"project" => !empty($this->project) ? $this->project->render($request, $response, $acl, true) : null,
				"user" => !empty($this->user) ? $this->user->render($request, $response, $acl, true) : null,
				"year" => !empty($this->year) ? (string)$this->year : null,
				"created" => $this->created,
				"read" => $this->read
			)
		));
	}

	/**
	 * @param Model_User $user
	 * @throws Exception
	 * @return string
	 */
	protected function renderNotificationString(Model_User $user)
	{
		$strings = static::$typeStrings[$this->type];

		if (empty($user))
		{
			if (array_key_exists("actor", $strings))
			{
				if ($user->getId() == $this->actor->getId())
				{
					$string = $strings["actor"];
				}
			}
			elseif (array_key_exists("supervisor", $strings) && !empty($this->project))
			{
				if ($this->project->getSupervisor()->getId() == $user->getId())
				{
					$string = $strings["supervisor"];
				}
			}
			elseif (array_key_exists("group_member", $strings) && !empty($this->group))
			{
				if ($this->project->getSupervisor()->getId() == $user->getId())
				{
					$string = $strings["supervisor"];
				}
			}
			elseif (array_key_exists("user", $strings) && !empty($this->user))
			{
				if ($this->user->getId() == $user->getId())
				{
					$string = $strings["user"];
				}
			}
		}

		if (empty($string))
		{
			$string = $strings["default"];
		}

		return strtr($string, array(
			"ACTOR_NAME" => !empty($this->actor) ? $this->actor->getName() : 'NULL',
			"GROUP_NAME" => !empty($this->group) ? $this->group->getName() : 'NULL',
			"PROJECT_NAME" => !empty($this->project) ? $this->project->getName() : 'NULL',
			"USER_NAME" => !empty($this->user) ? $this->user->getName() : 'NULL',
			"YEAR" => !empty($this->year) ? (string)$this->year : '0000',
		));
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
				"INSERT INTO `Notification` (`type`, `actor_id`, `group_id`, `intent_id`, `project_id`, `user_id`, `year`)
				 VALUES (?, ?, ?, ?, ?, ?, ?)",
				"siiiiii"
			)->execute(
				$this->type, $this->actor->getId(),
				!empty($this->group) && is_object($this->group) ? $this->group->getId() : null,
				!empty($this->intent) && is_object($this->intent) ? $this->intent->getId() : null,
				!empty($this->project) && is_object($this->project) ? $this->project->getId() : null,
				!empty($this->user) && is_object($this->user) ? $this->user->getId() : null,
				!empty($this->year) && is_object($this->year) ? $this->year->getId() : null
			);
			$this->id = $result->insert_id;
			$this->created = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}

	/**
	 * @param Model_Group $group
	 * @return void
	 */
	public function setGroup(Model_Group $group)
	{
		$this->group = $group;
	}

	/**
	 * @param Model_Intent $intent
	 * @return void
	 */
	public function setIntent(Model_Intent $intent)
	{
		$this->intent = $intent;
	}

	/**
	 * @param Model_Project $project
	 * @return void
	 */
	public function setProject(Model_Project $project)
	{
		$this->project = $project;
	}

	public function setRead($read)
	{
		$this->read = !empty($read) ? $read : null;
	}

	/**
	 * @param Model_User $user
	 * @return void
	 */
	public function setUser(Model_User $user)
	{
		$this->user = $user;
	}

	/**
	 * @param Model_Year $year
	 * @return void
	 */
	public function setYear(Model_Year $year)
	{
		$this->year = $year;
	}
}