<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class UserModelPermissions
 * Defines permissions a user has on a certain model.
 */
abstract class UserModelPermissions
{
	/**
	 * The template of permissions to follow.
	 * @var array
	 */
	private static $permissionsTemplate = array(
		"create" => false, "read" => false, "update" => false, "delete" => false
	);

	/**
	 * @var bool
	 */
	protected $global;
	/**
	 * @var Model
	 */
	protected $model;
	/**
	 * @var array
	 */
	protected $permissions = array();
	/**
	 * @var string
	 */
	private $queryField;
	/**
	 * @var string
	 */
	private $queryTable;
	/**
	 * @var Model_User
	 */
	protected $user;

	/**
	 * @param Model_User $user
	 * @param Model $model
	 * @param bool $forGlobal
	 * @throws InvalidArgumentException
	 */
	public function __construct(Model_User $user, Model $model, $forGlobal)
	{
		$target = ucfirst(strtolower(str_replace(get_class($model), "Model_", "")));

		if (empty($user))
		{
			throw new InvalidArgumentException("Missing User argument for " . __CLASS__);
		}
		elseif (empty($model) && !$forGlobal)
		{
			throw new InvalidArgumentException("Missing $target argument for " . __CLASS__);
		}
		elseif (!empty($model) && $forGlobal)
		{
			throw new InvalidArgumentException(
				"The $target argument should be NULL when fetching global permissions, in " . __CLASS__
			);
		}

		$this->user = $user;
		$this->model = $model;
		$this->global = $forGlobal;
		$this->permissions = static::$permissionsTemplate;

		$this->queryTable = "User_{$target}_Permissions";
		$this->queryField = strtolower($target) . "_id";
	}

	/**
	 * Can the user create a certain model?
	 * @return bool
	 */
	public function canCreate()
	{
		return array_key_exists("create", $this->permissions) && ($this->permissions["create"] == true);
	}

	/**
	 * Can the user delete a certain model?
	 * @return bool
	 */
	public function canDelete()
	{
		return array_key_exists("delete", $this->permissions) && ($this->permissions["delete"] == true);
	}

	/**
	 * Can the user read a certain model?
	 * @return bool
	 */
	public function canRead()
	{
		return array_key_exists("read", $this->permissions) && ($this->permissions["read"] == true);
	}

	/**
	 * Can the user update a certain model?
	 * @return bool
	 */
	public function canUpdate()
	{
		return array_key_exists("update", $this->permissions) && ($this->permissions["update"] == true);
	}

	/**
	 * Clear all the permissions stored.
	 *
	 * @param bool $skipSave
	 * @return void
	 */
	public function clear($skipSave = false)
	{
		// TODO: Clear Permissions
		if ($skipSave === false)
		{
			$this->save();
		}
	}

	/**
	 * Save the permissions we have.
	 * @return void
	 */
	public function save()
	{
		// TODO: Save permissions.
	}

	/**
	 * Set an array of the values.
	 *
	 * @param array $options
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function setAll(array $options)
	{
		$diffKeys = array_diff_key(static::$permissionsTemplate, $options);
		if (!empty($diffKeys))
		{
			throw new InvalidArgumentException("Invalid keys: " . implode(", ", array_keys($diffKeys)));
		}
		// This needs doing.
	}

	/**
	 * @param bool $option
	 * @return void
	 */
	public function setCreate($option)
	{
		$option = !empty($option);
		// This needs doing.
	}

	/**
	 * @param bool $option
	 * @return void
	 */
	public function setDelete($option)
	{
		$option = !empty($option);
		// This needs doing.
	}

	/**
	 * @param bool $option
	 * @return void
	 */
	public function setRead($option)
	{
		$option = !empty($option);
		// This needs doing.
	}

	/**
	 * @param bool $option
	 * @return void
	 */
	public function setUpdate($option)
	{
		$option = !empty($option);
		// This needs doing.
	}
}