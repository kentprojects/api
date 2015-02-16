<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Project extends Model
{
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

		return Database::prepare(
			"SELECT
				`project_id` AS 'id',
				`year`,
				`group_id` AS 'group',
				`name`,
				`slug`,
				`creator_id` AS 'creator',
				`created`,
				`updated`,
				`status`
			 FROM `Project`
			 WHERE `group_id` = ?",
			"i", __CLASS__
		)->execute($group->getId())->singleton();
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
		// $project = parent::getById($id);
		if (empty($project))
		{
			$statement = Database::prepare(
				"SELECT
					`project_id` AS 'id',
					`year`,
					`group_id` AS 'group',
					`name`,
					`slug`,
					`creator_id` AS 'creator',
					`created`,
					`updated`,
					`status`
				 FROM `Project`
			 	 WHERE `project_id` = ?",
				"i", __CLASS__
			);
			$project = $statement->execute($id)->singleton();
			if (!empty($project))
			{
				// Cache::set($project->getCacheName(), $project);
			}
		}
		return $project;
	}

	/**
	 * @param Model_Year $year
	 * @param string $slug
	 * @return boolean
	 */
	public static function validate(Model_Year $year, $slug)
	{
		$statement = Database::prepare("SELECT `project_id` FROM `Project` WHERE `year` = ? AND `slug` = ?", "is");
		$project_id = $statement->execute($year->getId(), $slug)->singleval();
		return $project_id === null;
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
	 * @var string
	 */
	protected $slug;
	/**
	 * @var Model_User
	 */
	protected $creator;
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

	public function __construct(Model_Year $year = null, $name = null, $slug = null, Model_User $creator = null)
	{
		if ($this->getId() !== null)
		{
			/** @noinspection PhpParamsInspection */
			$this->year = Model_Year::getById($this->year);
			/** @noinspection PhpParamsInspection */
			$this->creator = Model_User::getById($this->creator);
		}
		else
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

			if (empty($slug))
			{
				trigger_error("Missing SLUG passed to the PROJECT constructor", E_USER_ERROR);
			}
			$this->slug = $slug;

			if (empty($creator))
			{
				trigger_error("Missing CREATOR passed to the PROJECT constructor", E_USER_ERROR);
			}
			$this->creator = $creator;
		}

		parent::__construct();
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
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
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
	 * @return Model_Year
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->validateFields(array_merge(
			parent::jsonSerialize(),
			array(
				"year" => (string)$this->year,
				"group" => $this->group,
				"name" => $this->name,
				"slug" => $this->slug,
				"description" => $this->getDescription(),
				"creator" => $this->creator,
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
				"INSERT INTO `Project` (`year`, `group_id`, `name`, `slug`, `creator_id`, `created`)
				 VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
				"iissi"
			)->execute(
				(string)$this->year, (!empty($this->group) ? $this->group->getId() : null), $this->name,
				$this->slug, $this->creator->getId()
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			if (!empty($this->group))
			{
				$group_id = ($this->group instanceof Model_Group) ? $this->group->getId() : $this->group;
			}
			else
			{
				$group_id = null;
			}
			Database::prepare(
				"UPDATE `Project`
				 SET `year` = ?, `group_id` = ?, `name` = ?, `slug` = ?, `creator_id` = ?
				 WHERE `project_id` = ?",
				"iissii"
			)->execute(
				(string)$this->year, $group_id, $this->name, $this->slug, $this->creator->getId(),
				$this->id
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
		$this->metadata->description = $description;
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