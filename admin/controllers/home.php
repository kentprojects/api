<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Admin_Controller_Home extends Admin_Controller
{
	public function action_index()
	{
		$this->response->render(new PretendView());
	}
}