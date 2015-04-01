<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Trait Entity
 * A trait allows methods to be "included" in classes, to allow functionality to be grouped into similar traits.
 * http://php.net/manual/en/language.oop5.traits.php
 */
trait Entity
{
	/**
	 * Ensure that a particular root string is valid.
	 *
	 * @param $root
	 * @return string
	 */
	protected function validateRoot($root)
	{
		$split = explode("/", $root);
		if (count($split) !== 2)
		{
			throw new InvalidArgumentException("Invalid root format.");
		}

		list($entity, $id) = $split;

		if (empty($entity))
		{
			throw new InvalidArgumentException("Invalid root entity.");
		}
		elseif (!empty($this->allowedEntities) && !in_array($entity, $this->allowedEntities))
		{
			throw new InvalidArgumentException("Invalid root entity.");
		}
		elseif (!is_numeric($id))
		{
			throw new InvalidArgumentException("Invalid root entity ID.");
		}

		return "{$entity}/{$id}";
	}
}