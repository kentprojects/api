<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Admin_Controller_Error extends Admin_Controller
{
	public function action(Exception $e)
	{
		$this->response->render(new ErrorPage($e));
	}
}