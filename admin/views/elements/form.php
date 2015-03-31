<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Form
 * This represents a form that holds inputs and such.
 */
class Form extends HtmlElement
{
	/**
	 * Creates a new Form.
	 *
	 * @param string $action
	 * @param string $method
	 * @param array $attributes
	 * @param array $elements
	 * @throws InvalidArgumentException
	 */
	public function __construct($action, $method = null, array $attributes = array(), array $elements = array())
	{
		if (empty($method))
		{
			$method = Request::POST;
		}
		if (!in_array($method, array(Request::GET, Request::POST)))
		{
			throw new InvalidArgumentException("A form's method should be GET or POST.");
		}

		parent::__construct("form", $attributes);

		if (!empty($elements))
		{
			foreach ($elements as $element)
			{
				$this->addElement($element);
			}
		}
	}
}