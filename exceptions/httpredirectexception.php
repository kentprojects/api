<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @category: Exceptions
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class HttpRedirectException extends Exception
{
	protected $location;

	/**
	 * @param int $code
	 * @param string $location
	 */
	public function __construct($code, $location)
	{
		parent::__construct("Redirecting to $location", $code);
		$this->location = $location;
	}

	/**
	 * @return string
	 */
	public function getLocation()
	{
		return $this->location;
	}
}