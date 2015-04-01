<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Admin_Controller_Home
 * Handle the homepage of the Admin panel.
 */
class Admin_Controller_Home extends Admin_Controller
{
	/**
	 * Load the homepage.
	 * @return void
	 */
	public function action_index()
	{
		$this->response->render(new PretendView());
	}
}