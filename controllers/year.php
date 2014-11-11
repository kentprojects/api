<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Year extends Controller
{
	/**
	 * /year
	 * /year/:id
	 */
	public function action_index()
	{
		if ($this->request->getMethod() === Request::POST)
		{
			$this->createYear();
			return;
		}

		if ($this->request->getMethod() !== Request::GET)
		{
			throw new HttpStatusException(501);
		}

		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No year provided.");
		}

		$year = Model_Year::getById($this->request->param("id"));
		if (empty($year))
		{
			throw new HttpStatusException(404, "Year not found.");
		}

		$this->response->status(200);
		$this->response->body($year);
	}

	/**
	 * /year/staff
	 * /year/:id/staff
	 */
	public function action_staff()
	{
		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "No year provided.");
		}
	}

	/**
	 * A standalone method to "create" a new year.
	 * Happy new year! ^_^
	 */
	protected function createYear()
	{

	}
}