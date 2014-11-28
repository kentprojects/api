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
		$this->response->status(200);
		$this->response->body("Welcome to the KentProjects API!");
	}
}