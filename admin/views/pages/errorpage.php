<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class ErrorPage
 * This represents a page for the end user to see when errors occur.
 */
class ErrorPage extends View
{
	/**
	 * Build a new Error page.
	 *
	 * @param Exception $e
	 */
	public function __construct(Exception $e)
	{

	}
}