<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class KentProjects_TestBase
 * This is a base test class for all KentProjects tests.
 */
abstract class KentProjects_TestBase extends PHPUnit_Framework_TestCase
{
	/**
	 * Asserts that a model is equal to another model.
	 *
	 * @param Model $expected
	 * @param Model $actual
	 * @param string $message
	 * @return void
	 */
	public function assertEqualsModel(Model $expected, Model $actual, $message = null)
	{
		if (empty($message))
		{
			$message = "Failed asserting that " . get_class($actual) . " is " . get_class($expected) . ".";
		}
		$this->assertTrue((get_class($expected) === get_class($actual)) && ($expected->getId() == $actual->getId()), $message);
	}
}