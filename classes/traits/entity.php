<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
trait Entity
{
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