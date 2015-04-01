<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Input
 * This represents a simple Input tag.
 */
class Input extends HtmlElement
{
	/**
	 * @param string $name
	 * @param array $attributes
	 */
	public function __construct($name, array $attributes = array())
	{
		$attributes = array_merge(array(
			"name" => $name
		), $attributes);

		parent::__construct("input", $attributes);
	}

	/**
	 * Validate a particular value against this field.
	 *
	 * @param mixed $value
	 * @throws ValidationException
	 * @return bool
	 */
	public function validate($value)
	{
		return true;
	}
}