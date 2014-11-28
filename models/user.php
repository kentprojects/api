<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Model_User extends Model_Abstract
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
		$statement = Database::prepare(
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
		);
		return $statement->execute($id)->singleton();
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function isConvener()
	{
		$state = $this->metadata->convenerstate;
		return !empty($state);
	}

	/**
	 * @return bool
	 */
	public function isSecondMarker()
	{
		$state = $this->metadata->secondmarkerstate;
		return !empty($state);
	}

	/**
	 * @return bool
	 */
	public function isStaff()
	{
		return $this->role === "student";
	}

	/**
	 * @return bool
	 */
	public function isStudent()
	{
		return $this->role === "student";
	}

	/**
	 * @return bool
	 */
	public function isSupervisor()
	{
		$state = $this->metadata->supervisorstate;
		return !empty($state);
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
				"first_name" => $this->first_name,
				"last_name" => $this->last_name,
				"role" => $this->role
			),
			($this->isStaff()
				? array(
					"is" => array(
						"convenor" => $this->isConvener(),
						"secondmarker" => $this->isSecondMarker(),
						"supervisor" => $this->isSupervisor()
					)
				)
				: array()
			),
			array(
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