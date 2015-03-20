<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class InputPassword extends Input
{
	public function __construct($name, array $attributes = array())
	{
		parent::__construct($name, array_merge($attributes, array(
			"type" => "password"
		)));
	}

	/**
	 * Validate a particular value against this field.
	 *
	 * @param mixed $value
	 * @throws FormException
	 * @return bool
	 */
	public function validate($value)
	{
		return Validate::Password($value);
	}
}