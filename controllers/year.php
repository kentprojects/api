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
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::POST);

		if ($this->request->getMethod() === Request::POST)
		{
			/**
			 * POST /year
			 * Used to create a new year!
			 * Happy new year! ^_^
			 */

			if (!$this->auth->hasUser())
			{
				throw new HttpStatusException(401, "You must be authorized as a user to do this.");
			}

			/**
			 * Validate that the user can create a new year.
			 */
			$this->validateUser(array(
				"entity" => "year",
				"action" => ACL::CREATE,
				"message" => "You do not have permission to create a new year."
			));

			$year = Model_Year::getById(
				Database::prepare("CALL usp_CreateNewAcademicYear(?)", "i")
					->execute($this->auth->getUser()->getId())->singleval()
			);

			$this->response->status(201);
			$this->response->body($year);

			return;
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

		/**
		 * GET /year/:id
		 * Get a year.
		 */

		$this->response->status(200);
		$this->response->body($year);
	}

	/**
	 * /year/:id/staff
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_staff()
	{
		$this->validateMethods(Request::GET, Request::POST, Request::DELETE);

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
			 * POST|DELETE /year/:id/staff
			 * Adding / removing staff to a year!
			 */

			/**
			 * Validate that the user can update this year.
			 */
			$this->validateUser(array(
				"entity" => "year/" . $year->getId(),
				"action" => ACL::UPDATE,
				"message" => "You do not have permission to update this year.",
				"role" => "staff"
			));

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

		/**
		 * GET /year/:id/staff
		 */

		$this->response->status(200);
		$this->response->body($year->getStaff());
	}

	/**
	 * /year/:id/stats
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_stats()
	{
		$this->validateMethods(Request::GET);

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
		$this->response->body(Model_Stats::getForYear($year));
	}
}