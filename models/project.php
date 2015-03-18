<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Project extends Model
{
	/**
	 * @param Model_Project $project
	 * @return void
	 */
	public static function delete(Model_Project $project)
	{
		$project->getGroup();
		Database::prepare("DELETE FROM `Project` WHERE `project_id` = ?", "i")->execute($project->getId());
		$project->clearCaches();
	}

	/**
	 * Get the relevant Project by it's group.
	 *
	 * @param Model_Group $group
	 * @return Model_Project
	 */
	public static function getByGroup(Model_Group $group)
	{
		if ($group->getId() === null)
		{
			return null;
		}

		$id = Cache::get($group->getCacheName("project"));
		if (empty($id))
		{
			$id = Database::prepare("SELECT `project_id` FROM `Project` WHERE `group_id` = ? AND `status` = 1", "i")
				->execute($group->getId())->singleval();
			!empty($id) && Cache::set($group->getCacheName("project"), $id, Cache::HOUR);
		}
		return !empty($id) ? static::getById($id) : null;
	}

	/**
	 * Get the relevant Project by it's ID.
	 *
	 * @param int $id
	 * @return Model_Project
	 */
	public static function getById($id)
	{
		/** @var Model_Project $project */
		$project = parent::getById($id);
		if (empty($project))
		{
			$project = Database::prepare(
				"SELECT
					`project_id` AS 'id',
					`year`,
					`group_id` AS 'group',
					`name`,
					`creator_id` AS 'creator',
					`supervisor_id` AS 'supervisor',
					`created`,
					`updated`,
					`status`
				 FROM `Project`
			 	 WHERE `project_id` = ?",
				"i", __CLASS__
			)->execute($id)->singleton();
			Cache::store($project);
		}
		return $project;
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var Model_Year
	 */
	protected $year;
	/**
	 * @var Model_Group
	 */
	protected $group;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var Model_User
	 */
	protected $creator;
	/**
	 * @var Model_User
	 */
	protected $supervisor;
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

	public function __construct(Model_Year $year = null, $name = null, Model_User $creator = null)
	{
		if ($this->getId() === null)
		{
			if (empty($year))
			{
				trigger_error("Missing YEAR passed to the PROJECT constructor", E_USER_ERROR);
			}
			$this->year = $year;

			if (empty($name))
			{
				trigger_error("Missing NAME passed to the PROJECT constructor", E_USER_ERROR);
			}
			$this->name = $name;

			if (empty($creator))
			{
				trigger_error("Missing CREATOR passed to the PROJECT constructor", E_USER_ERROR);
			}
			$this->creator = $creator;
		}
		parent::__construct();
	}

	/**
	 * @return array
	 */
	public function clearCacheStrings()
	{
		$groupCaches = array();
		if (!empty($this->group))
		{
			if (is_numeric($this->group))
			{
				/** @noinspection PhpToStringImplementationInspection */
				$groupCaches[] = Model_Group::cacheName() . "." . $this->group;
			}
			else
			{
				$groupCaches[] = $this->group->clearCacheStrings();
			}
		}
		return array_merge(
			parent::clearCacheStrings(),
			$groupCaches// , $this->getCacheName("project")
		);
	}

	/**
	 * @return string
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return Model_User
	 */
	public function getCreator()
	{
		if (!empty($this->creator) && is_numeric($this->creator))
		{
			/** @noinspection PhpParamsInspection */
			$this->creator = Model_User::getById($this->creator);
		}
		return $this->creator;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->metadata->description;
	}

	public function getGroup()
	{
		if (!empty($this->group) && is_numeric($this->group))
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
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return Model_User
	 */
	public function getSupervisor()
	{
		if (!empty($this->supervisor) && is_numeric($this->supervisor))
		{
			/** @noinspection PhpParamsInspection */
			$this->supervisor = Model_User::getById($this->supervisor);
		}
		return $this->supervisor;
	}

	/**
	 * @return string
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * @return Model_Year
	 */
	public function getYear()
	{
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
	 * Remove the group from the project.
	 * @return void
	 */
	public function removeGroup()
	{
		$this->group = null;
	}

	/**
	 * Render the project.
	 *
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		$this->getCreator();
		$this->getSupervisor();

		return $this->validateFields(array_merge(
			parent::render($request, $response, $acl, $internal),
			array(
				"year" => (string)$this->year,
				"group" => is_object($this->group) ? $this->group->render($request, $response, $acl, "project") : $this->group,
				"name" => $this->name,
				"description" => $this->getDescription(),
				"creator" => $this->creator->render($request, $response, $acl, true),
				"supervisor" => $this->supervisor->render($request, $response, $acl, true),
				"permissions" => $acl->get($this->getEntityName()),
				"created" => $this->created,
				"updated" => $this->updated
			)
		));
	}

	public function save()
	{
		if (empty($this->id))
		{
			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `Project` (`year`, `group_id`, `name`, `creator_id`, `supervisor_id`, `created`)
				 VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
				"iisii"
			)->execute(
				(string)$this->year, (!empty($this->group) ? $this->group->getId() : null), $this->name,
				$this->creator->getId(), $this->supervisor->getId()
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			$this->getGroup();
			$this->getSupervisor();

			Database::prepare(
				"UPDATE `Project`
				 SET `year` = ?, `group_id` = ?, `name` = ?, `supervisor_id` = ?
				 WHERE `project_id` = ?",
				"iisii"
			)->execute(
				(string)$this->year, !empty($this->group) ? $this->group->getId() : null, $this->name,
				$this->supervisor->getId(), $this->id
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

	/**
	 * @param Model_Group $group
	 * @return void
	 */
	public function setGroup(Model_Group $group)
	{
		$this->group = $group;
	}

	public function setSupervisor(Model_User $supervisor)
	{
		/**
		 * TODO: Validate this user is a registered supervisor of this year.
		 */
		$this->supervisor = $supervisor;
	}

	/**
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function update(array $data)
	{
		if (!empty($data["description"]))
		{
			$this->setDescription($data["description"]);
		}
	}
}