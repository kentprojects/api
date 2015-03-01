<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Admin_Controller
{
	/**
	 * @var Model_User
	 */
	protected $currentUser;
	/**
	 * @var Request_Internal
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @param Admin_Request $request
	 * @param Admin_Response $response
	 */
	public function __construct(Admin_Request &$request, Admin_Response &$response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	private function getCurrentUser()
	{
		$this->currentUser = Session::get("user");
		if (empty($this->currentUser))
		{
			/**
			 * Start panicking?
			 */
		}
	}

	/**
	 * To be run BEFORE the main action.
	 */
	public function before()
	{
		Timing::start("controller");

		$this->getCurrentUser();
	}

	/**
	 * To be run AFTER the main action.
	 */
	public function after()
	{
		Timing::stop("controller");
	}
}