<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class LoginPage
 * This represents a page for the end user to login to the Admin area.
 */
class LoginPage extends View
{
	/**
	 * Create a new Login page.
	 *
	 * @param LoginForm $loginForm
	 */
	public function __construct(LoginForm $loginForm)
	{
		$this->setTitle("Login");
		$this->addViewChild($loginForm);
	}

	/**
	 * Items to render the top of the Login page.
	 * @return void
	 */
	public function renderTop()
	{
		parent::renderTop();
		echo
		'<div class="container">',
		'<div class="row">',
		'<div class="col-xs-12 col-sm-4 col-sm-offset-4">';
	}

	/**
	 * Items to render at the bottom of the Login page.
	 * @return void
	 */
	public function renderBottom()
	{
		echo
		'</div>',
		'</div>',
		'</div>';
		parent::renderBottom();
	}
}