<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class LoginForm
 * This represents a form used to login.
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