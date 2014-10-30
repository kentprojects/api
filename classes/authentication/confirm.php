<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
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

		// Validate the auth code

		// Return the user data?
	}
}