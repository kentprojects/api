<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Admin_Controller_Login
 *
 */
class Admin_Controller_Login extends Admin_Controller
{
	/**
	 * This is here to override the authentication, since the Login controller needs to be accessible to the public
	 *   (y'know, because they need to login!)
	 */
	public function before()
	{
		/**
		 * Maybe something here to stop already-authenticated people visiting?
		 */
	}

	/**
	 * /login
	 *
	 * The main action to be run when a user wants to login.
	 *
	 * @throws Exception
	 * @return void
	 */
	public function action_index()
	{
		$form = new LoginForm("/login");

		$this->response->render(new LoginPage($form));
	}
}