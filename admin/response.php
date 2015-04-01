<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Admin_Response
 * This represents an internal Response from an Admin controller.
 */
class Admin_Response extends Response
{
	/**
	 * Set the response body with a rendered view.
	 *
	 * @param View $view
	 * @throws Exception
	 * @return void
	 */
	public function render(View $view)
	{
		ob_start();
		try
		{
			$view->render();
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
		$this->body(ob_get_clean());
	}
}