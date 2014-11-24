<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Project extends Model_Abstract
{
	/**
	 * Get the relevant Project by it's ID.
	 *
	 * @param int $id
	 * @return Model_Project
	 */
	public static function getById($id)
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
		return $statement->execute($id)->singleton();
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

	public function __construct(Model_Year $year, $name, $slug, Model_User $creator)
	{
		if ($this->getId() !== null)
		{
			return;
		}

		$this->year = $year;
		$this->name = $name;
		$this->slug = $slug;
		$this->creator = $creator;
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
			if (empty($this->year))
			{
				throw new InvalidArgumentException("No year has been set for the project.");
			}
			if (empty($this->name))
			{
				throw new InvalidArgumentException("No name has been set for the project.");
			}
			if (empty($this->slug))
			{
				throw new InvalidArgumentException("No slug has been set for the project.");
			}
			if (empty($this->creator))
			{
				throw new InvalidArgumentException("No creator has been set for the project.");
			}

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
			Database::prepare(
				"UPDATE `Project`
				 SET `year` = ?, `group_id` = ?, `name` = ?, `slug` = ?, `creator_id` = ?
				 WHERE `project_id` = ?",
				"iissi"
			)->execute(
				(string)$this->year, (!empty($this->group) ? $this->group->getId() : null), $this->name,
				$this->slug, $this->creator->getId(), $this->id
			);
			$this->updated = Date::format(Date::TIMESTAMP, time());
		}
		parent::save();
	}

	/**
	 * @param Model_User $creator
	 */
	public function setCreator(Model_User $creator)
	{
		$this->creator = $creator;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		$this->slug = slugify($name);
	}

	/**
	 * @param Model_Year $year
	 */
	public function setYear(Model_Year $year)
	{
		$this->year = $year;
	}
}