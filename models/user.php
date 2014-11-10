<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
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
	 * Get the relevant User by their email.
	 *
	 * @param string $email
	 * @return User|null
	 */
	public static function getByEmail($email)
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
			 WHERE `email` = ?",
			"s", __CLASS__
		);
		return $statement->execute($email)->singleton();
	}
	
	/**
	 * Get the relevant User by it's ID.
	 *
	 * @param int $id
	 * @return User|null
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
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array_merge(
			parent::jsonSerialize(),
			array(
				"email" => $this->email,
				"first_name" => $this->first_name,
				"last_name" => $this->last_name,
				"role" => $this->role,
				"created" => $this->created,
				"lastlogin" => $this->lastlogin,
				"updated" => $this->updated
			)
		);
	}
	
	/**
	 * @return void
	 */
	public function save()
	{
		if (empty($this->id))
		{
			if (empty($this->email))
			{
				// Throw an error. A big one.
			}
			if (empty($this->role))
			{
				// Throw an error. A substantial one.
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