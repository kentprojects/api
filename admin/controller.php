<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * This represents an Admin controller.
 * It's different from a regular controller since it doesn't rely on signed URLs and JSON output.
 */
abstract class Admin_Controller
{
	/**
	 * The currently logged in user.
	 * @var Model_User
	 */
	protected $currentUser;
	/**
	 * The current request.
	 * @var Admin_Request
	 */
	protected $request;
	/**
	 * The current response.
	 * @var Admin_Response
	 */
	protected $response;

	/**
	 * Build a new Admin controller.
	 *
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
	 * @return void
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
	 * @return void
	 */
	public function after()
	{
		$this->response->status(200);
		Timing::stop("controller");
	}
}