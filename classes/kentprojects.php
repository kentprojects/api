<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * A collection of global functions to assist in the running of KentProjects.
 */
final class KentProjects
{
	/**
	 * @var ACL
	 */
	public static $acl;

	/**
	 * Get the ACL list for an entity.
	 *
	 * @param string $entity
	 * @return array
	 */
	public static function ACL($entity)
	{
		return empty(static::$acl) ? array() : static::$acl->get($entity);
	}
}