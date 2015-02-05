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
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET);

		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No id required.");
		}

		$years = new UserYearMap($this->auth->getUser());

		$this->response->status(200);
		$this->response->body($years);
	}
}