<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class LoginForm extends Form
{
	public function __construct($action)
	{
		parent::__construct(
			$action, null,
			array(
				"class" => "form-horizontal"
			),
			array(
				new InputEmail("email", array("id" => "inputEmail")),
				new InputPassword("password", array("id" => "inputPassword"))
			)
		);
	}
}