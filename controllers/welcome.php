<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Welcome extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;

	/**
	 * /
	 */
	public function action_index()
	{
		$user = $this->auth->getUser();

		$this->response->status(200);
		$this->response->body("Welcome to the KentProjects API" . (!empty($user) ? ", " . $user->getName() : "!"));
	}
}