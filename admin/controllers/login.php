<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Admin_Controller_Login extends Admin_Controller
{
	public function before()
	{
	}

	/**
	 * /login
	 *
	 * @throws Exception
	 */
	public function action_index()
	{
		$form = new LoginForm("/login");

		$this->response->render(new LoginPage($form));
	}
}