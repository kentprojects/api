<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Years extends Controller
{
	/**
	 * /years
	 */
	public function action_index()
	{
		if ($this->request->getMethod() !== Request::GET)
		{
			throw new HttpStatusException(501);
		}

		$this->response->status(200);
		$this->response->body(Model_Year::getAll());
	}
}