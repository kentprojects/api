<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_User extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::APP;

	/**
	 * /user
	 * /user/:id
	 *
	 * Gets an individual user object for an unknown user.
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET);
		if ($this->request->param("id") === null)
		{
			throw new HttpStatusException(400, "An ID should be passed to the USER controller.");
		}

		$user = Model_User::getById($this->request->param("id"));
		if (empty($user))
		{
			throw new HttpStatusException(404, "User not found.");
		}

		$this->response->status(200);
		$this->response->body($user);
	}
}