<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
abstract class Authentication_Abstract
{
	/**
	 * @var Model_Application
	 */
	protected $application;
	/**
	 * @var Auth
	 */
	protected $auth;
	/**
	 * @var string
	 */
	protected $authentication = Auth::USER;
	/**
	 * @var Request_Internal
	 */
	protected $request;
	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @throws HTTPStatusException
	 */
	public function __construct(Request_Internal &$request, Response &$response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * The main action this authentication provider uses.
	 *
	 * @throws HttpRedirectException
	 * @throws HttpStatusException
	 * @return void
	 */
	public abstract function action();
}