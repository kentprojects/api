<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class ACL implements Countable
{
	const CREATE = "acl:create";
	const READ = "acl:read";
	const UPDATE = "acl:update";
	const DELETE = "acl:delete";

	protected static $template = array("create" => 0, "read" => 0, "update" => 0, "delete" => 0);

	protected $acl;
	protected $user;

	public function __construct(Model_User $user = null)
	{
		if (empty($user))
		{
			return;
		}

		$this->user = $user;
		$this->fetch();
	}

	/**
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
	 * @return int
	 */
	public function count()
	{
		return empty($this->acl) ? 0 : count($this->acl);
	}

	public function delete($entity)
	{
		unset($this->acl[$entity]);
		ksort($this->acl);
	}

	public function fetch()
	{
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