<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ACL
 * This is the glorious ACL class that ensures a user can and can't do some action.
 */
final class ACL implements Countable
{
	const CREATE = "acl:create";
	const READ = "acl:read";
	const UPDATE = "acl:update";
	const DELETE = "acl:delete";

	/**
	 * A base template for each set of ACLs.
	 * @var array
	 */
	protected static $template = array("create" => 0, "read" => 0, "update" => 0, "delete" => 0);

	/**
	 * The current list of ACLs.
	 * @var array
	 */
	protected $acl;
	/**
	 * The current user we're handling ACLs for.
	 * @var Model_User
	 */
	protected $user;

	/**
	 * Build a new ACLs object.
	 *
	 * @param Model_User $user
	 */
	public function __construct(Model_User $user = null)
	{
		/**
		 * If we don't have user, just return, because it will just return false for everything.
		 */
		if (empty($user))
		{
			return;
		}

		$this->user = $user;
		$this->fetch();
	}

	/**
	 * Check that a particular entity is a match.
	 *
	 * @param string $entry
	 * @return array
	 */
	protected function checkMatch($entry)
	{
		foreach ($this->acl as $entity => $acl)
		{
			if ($entity === $entry)
			{
				return $acl;
			}
		}
		return array();
	}

	/**
	 * Count the number of ACLs.
	 * @return int
	 */
	public function count()
	{
		return empty($this->acl) ? 0 : count($this->acl);
	}

	/**
	 * Remove a particular ACL from this list.
	 * @param string $entity
	 * @return void
	 */
	public function delete($entity)
	{
		unset($this->acl[$entity]);
		ksort($this->acl);
	}

	/**
	 * Build the list of ACLs for the current user.
	 * @return void
	 */
	public function fetch()
	{
		/**
		 * If we were not passed a user, then stop.
		 */
		if (empty($this->user))
		{
			return;
		}

		$this->acl = array();
		$control = Database::prepare(
			"SELECT `entity`, `create`, `read`, `update`, `delete` FROM `ACL` WHERE `user_id` = ?", "i"
		)->execute($this->user->getId())->as_assoc()->all();

		foreach ($control as $acl)
		{
			$entity = $acl["entity"];
			unset($acl["entity"]);
			$this->acl[$entity] = $acl;
		}

		ksort($this->acl);
	}

	/**
	 * Get the permissions for a particular entity.
	 * This "recursively" splits the entity to ensure that all global variants are handled.
	 *
	 * Thus,
	 *   group
	 *   group/1
	 *
	 *   project
	 *   project/22
	 *
	 * @param string $entity
	 * @return array
	 */
	public function get($entity)
	{
		if (empty($this->user))
		{
			return array();
		}

		$values = static::$template;

		if (empty($this->acl))
		{
			return $values;
		}

		$range = explode("/", $entity);
		$rangeString = "";

		foreach ($range as $i => $piece)
		{
			$rangeString .= ($i == 0 ? "" : "/") . $piece;
			$values = array_merge($values, $this->checkMatch($rangeString));
		}

		foreach ($values as $key => $value)
		{
			$values[$key] = boolval($value);
		}

		return $values;
	}

	/**
	 * Return the current user in question.
	 *
	 * @return Model_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Save the user's permissions.
	 * @return void
	 */
	public function save()
	{
		if (empty($this->user))
		{
			return;
		}

		Database::prepare("DELETE FROM `ACL` WHERE `user_id` = ?", "i")->execute($this->user->getId());

		$query = "INSERT " . "INTO `ACL` (`user_id`, `entity`, `create`, `read`, `update`, `delete`) VALUES ";
		$types = "";
		$values = array();
		$valueFields = array();

		foreach ($this->acl as $entity => $acl)
		{
			$valueFields[] = "(?, ?,?,?,?,?)";
			$types .= "isiiii";
			$values = array_merge($values, array(
				$this->user->getId(), $entity, $acl["create"], $acl["read"], $acl["update"], $acl["delete"]
			));
		}

		$statement = Database::prepare($query . implode(", ", $valueFields), $types);
		call_user_func_array(array($statement, "execute"), $values);
	}

	/**
	 * Update a user's permissions for something.
	 *
	 * @param string $entity
	 * @param bool $create
	 * @param bool $read
	 * @param bool $update
	 * @param bool $delete
	 * @return void
	 */
	public function set($entity, $create = false, $read = false, $update = false, $delete = false)
	{
		if (empty($this->user))
		{
			return;
		}

		$this->acl[$entity] = array(
			"create" => $create ? 1 : 0,
			"read" => $read ? 1 : 0,
			"update" => $update ? 1 : 0,
			"delete" => $delete ? 1 : 0
		);

		ksort($this->acl);
	}

	/**
	 * Validate a particular entity and action.
	 * This is mostly used in the controllers when validating a user's permission to do a certain action.
	 *
	 * @param string $entity
	 * @param string $action
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function validate($entity, $action)
	{
		if (strpos($action, "acl:") !== 0)
		{
			throw new InvalidArgumentException("Bad action ('{$action}') passed to ACL::validate.");
		}

		if (empty($this->user))
		{
			return false;
		}

		$action = str_replace("acl:", "", $action);
		$control = $this->get(strtolower($entity));

		return array_key_exists($action, $control) && ($control[$action] === true);
	}
}