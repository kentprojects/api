<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Form extends HtmlElement
{
	/**
	 * @param string $action
	 * @param array $method
	 * @throws InvalidArgumentException
	 */
	public function __construct($action, $method = null)
	{
		if (empty($method))
		{
			$method = Request::POST;
		}
		if (!in_array($method, array(Request::GET, Request::POST)))
		{
			throw new InvalidArgumentException("A form's method should be GET or POST.");
		}
		parent::__construct("form");
	}
}