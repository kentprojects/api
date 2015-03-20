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
	 * @throws HttpStatusException
	 */
	public function __construct(Admin_Request &$request, Admin_Response &$response)
	{
		$this->request = $request;
		$this->response = $response;

		if (!in_array($this->request->getMethod(), array(Request::GET, Request::POST)))
		{
			throw new HttpStatusException(501, "This HTTP verb is not implemented, and never will be.");
		}
	}

	/**
	 * To be run BEFORE the main action.
	 *
	 * @throws HttpRedirectException
	 */
	public function before()
	{
		Timing::start("controller");

		/**
		 * Get the current user.
		 */
		$this->currentUser = Session::get("user");
		if (empty($this->currentUser))
		{
			/**
			 * Start panicking?
			 */
			throw new HttpRedirectException(302, "/login");
		}
	}

	/**
	 * To be run AFTER the main action.
	 */
	public function after()
	{
		$this->response->status(200);
		Timing::stop("controller");
	}
}