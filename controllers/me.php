<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Me extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::USER;

	/**
	 * /me
	 * /me/:id
	 *
	 * Get a collection of information for the current user.
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET);

		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No ID should be passed to the ME controller.");
		}

		$user = $this->auth->getUser();
		if ($user->isStudent())
		{
			$details = array(
				"group" => null,
				"project" => null,
				"user" => $user
			);

			$details["group"] = Model_Group::getByUser($user);
			if (!empty($details["group"]))
			{
				$details["project"] = Model_Project::getByGroup($details["group"]);
			}
		}
		else
		{
			$details = array(
				"user" => $user
			);
		}

		$this->response->status(200);
		$this->response->body($details);
	}
}