<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Admin_Controller_Error
 * Handle when exceptions are thrown on the Admin panel.
 */
class Admin_Controller_Error extends Admin_Controller
{
	/**
	 * The action to run when an exception is thrown.
	 *
	 * @param Exception $e
	 * @throws Exception
	 */
	public function action(Exception $e)
	{
		$this->response->render(new ErrorPage($e));
	}
}