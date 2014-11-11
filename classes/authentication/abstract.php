<?php
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
	
	/**
	 * The action to create a new API token.
	 *
	 * @return string
	 */
	protected function createApiToken(Model_User $user)
	{
		$break = false;
		$statement = Database::prepare("INSERT INTO `Token` (`user_id`, `token`) VALUES (?,?)", "is");
		while(!$break)
		{
			$token = md5(uniqid());
			try
			{
				$result = $statement->execute($user->getId(), $token);
				$break = true;
			}
			catch (DatabaseException $e)
			{
				// Work out if this is a duplicate key issue. If so, let it loop again. Else, throw the exception.
				if (true)
				{
					throw $e;
				}
			}
		}
		return $token;
	}
	
	/**
	 * The action to create a new Auth token.
	 *
	 * Auth Tokens are small arbitrary pieces of information, used to log people in.
	 * @return string
	 */
	protected function generateAuthToken(Model_User $user)
	{
		$break = false;
		$statement = Database::prepare("INSERT INTO `Authentication` (`user_id`, `token`) VALUES (?,?)", "is");
		while(!$break)
		{
			$token = md5(uniqid());
			try
			{
				$result = $statement->execute($user->getId(), $token);
				$break = true;
			}
			catch (DatabaseException $e)
			{
				// Work out if this is a duplicate key issue. If so, let it loop again. Else, throw the exception.
				if (true)
				{
					throw $e;
				}
			}
		}
		return $token;
	}
}