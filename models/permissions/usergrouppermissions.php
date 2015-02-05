<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class UserGroupPermissions
 * Defines permissions a user has on a group.
 */
class UserGroupPermissions extends UserModelPermissions
{
	/**
	 * @param Model_User $user
	 * @param Model_Group $group
	 * @param bool $forGlobal
	 */
	public function __construct(Model_User $user, Model_Group $group, $forGlobal = false)
	{
		parent::__construct($user, $group, $forGlobal);
	}
}