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
			/**
			 * Used to create a new year!
			 * Happy new year! ^_^
			 */

			if ($this->auth->getUser() === null)
			{
				throw new HttpStatusException(401, "You must be a user to do this.");
			}

			$user = $this->auth->getUser();
			if (!$user->isConvener())
			{
				throw new HttpStatusException(401, "You must be a convener to do this.");
			}

			$year = Model_Year::create();
			$this->response->status(201);
			$this->response->body($year);
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

		$year = Model_Year::getById($this->request->param("id"));
		if (empty($year))
		{
			throw new HttpStatusException(404, "Year not found.");
		}

		if (($this->request->getMethod() === Request::POST) || ($this->request->getMethod() === Request::DELETE))
		{
			/**
			 * Adding / removing staff to a year!
			 */
			if (is_array($_POST) || empty($_POST))
			{
				throw new InvalidArgumentException("POST is an ARRAY/EMPTY not a STRING.");
			}

			if ($this->auth->getUser() === null)
			{
				throw new HttpStatusException(401, "You must be a user to do this.");
			}

			$user = $this->auth->getUser();
			if (!$user->isConvener())
			{
				throw new HttpStatusException(401, "You must be a convener to do this.");
			}

			$_POST = json_decode($_POST);
			$failed = array();
			$staff = array();

			foreach ($_POST as $user_id)
			{
				$user = Model_Staff::getById($user_id);
				if (!empty($user))
				{
					$staff[] = $user;
				}
				else
				{
					$failed[] = $user_id;
				}
			}

			if (count($failed) > 0)
			{
				throw (new HttpStatusException(400, "Failed to find a valid staff account for the following IDs:"))
					->setName("InvalidStaffIDs")
					->setData($failed);
			}

			$method = ($this->request->getMethod() === Request::POST) ? "addStaff" : "removeStaff";

			foreach ($staff as $user)
			{
				$year->$method($user);
			}
		}

		$this->response->status(200);
		$this->response->body($year->getStaff());
	}
}