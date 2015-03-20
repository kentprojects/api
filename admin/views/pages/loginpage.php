<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class LoginPage extends View
{
	public function __construct(LoginForm $loginForm)
	{
		parent::__construct();
		$this->setTitle("Login");
		$this->addViewChild($loginForm);
	}

	public function renderTop()
	{
		parent::renderTop();
		echo
		'<div class="container">',
		'<div class="row">',
		'<div class="col-xs-12 col-sm-4 col-sm-offset-4">';
	}

	public function renderBottom()
	{
		echo
		'</div>',
		'</div>',
		'</div>';
		parent::renderBottom();
	}
}