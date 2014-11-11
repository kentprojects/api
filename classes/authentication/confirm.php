<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Authentication_Confirm extends Authentication_Abstract
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::APP;

	/**
	 * The main action this authentication provider uses.
	 *
	 * @throws HTTPStatusException
	 * @return void
	 */
	public function action()
	{
		if ($this->request->query("auth") === null)
		{
			throw new HTTPStatusException(400, "Missing state code.");
		}
		
		$user = $this->validateCode();
		if (empty($user))
		{
			throw new HTTPStatusException(400, "Invalid authentication token.");
		}
		
		$this->clearCode();
		
		$output = array(
			"token" => $this->createApiToken($user),
			"user" => $user
		);
		$this->response->status(200);
		$this->response->body(json_encode($output));
	}
	
	/**
	 * @return void
	 */
	private function clearCode()
	{
		Database::prepare("DELETE FROM `Authentication` WHERE `token` = ?", "s")->execute($this->request->query("auth"));
	}
	
	/**
	 * @return Model_User
	 */
	private function validateCode()
	{
		$statement = Database::prepare("SELECT `user_id` FROM `Authentication` WHERE `token` = ?", "s");
		$user_id = $statement->execute($this->request->query("auth"))->singleval();
		return (empty($user_id)) ? null : Model_User::getById($user_id);
	}
}