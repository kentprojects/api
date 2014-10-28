<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Authentication_Internal extends Authentication_Abstract
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;

	/**
	 * The main action this authentication provider uses.
	 *
	 * @throws HttpRedirectException
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action()
	{
		// TODO: Implement action() method.
	}
}