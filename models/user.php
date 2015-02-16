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
		$statement = Database::prepare("SELECT `user_id` FROM `User` WHERE `email` = ? AND `status` = 1", "s");
		$user_id = $statement->execute($email)->singleval();

		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}

	/**
	 * Get the relevant User by it's ID.
	 *
	 * @param int $id
	 * @return Model_User|null
	 */
	public static function getById($id)
	{
		return Database::prepare(
			"SELECT
				`user_id` AS 'id',
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
	}

	/**
	 * @var int
	 */
	protected $id;
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
	 * @var UserYearMap
	 */
	public $years;

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->metadata->description;
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
		return trim($this->first_name . " " . $this->last_name);
	}

	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}

	public function initYearMap()
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
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->validateFields(array_merge(
			parent::jsonSerialize(),
			array(
				"email" => $this->email,
				"name" => $this->getName(),
				"first_name" => $this->first_name,
				"last_name" => $this->last_name,
				"role" => $this->role
			),
			(!empty($this->years) ? $this->years : array()),
			array(
				"bio" => $this->getDescription(),
				"created" => $this->created,
				"lastlogin" => $this->lastlogin,
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
			if (empty($this->email))
			{
				throw new InvalidArgumentException("No email provided for the student.");
			}
			if (empty($this->role))
			{
				throw new InvalidArgumentException("No role provided for the student.");
			}

			/** @var _Database_State $result */
			$result = Database::prepare(
				"INSERT INTO `User` (`email`, `first_name`, `last_name`, `role`, `created`)
				 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)",
				"ssss"
			)->execute(
				$this->email, $this->first_name, $this->last_name, $this->role
			);
			$this->id = $result->insert_id;
			$this->created = $this->updated = Date::format(Date::TIMESTAMP, time());
		}
		else
		{
			Database::prepare(
				"UPDATE `User`
				 SET `email` = ?, `first_name` = ?, `last_name` = ?
				 WHERE `user_id` = ?",
				"sssi"
			)->execute(
				$this->email, $this->first_name, $this->last_name,
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

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function setFirstName($firstname)
	{
		$this->first_name = $firstname;
	}

	public function setLastName($lastname)
	{
		$this->last_name = $lastname;
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
}