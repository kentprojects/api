<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class RouterTest extends KentProjects_TestBase
{
	public function testController()
	{
		$this->assertEquals(
			array(
				"controller" => "project",
				"action" => "index"
			),
			Router::handle("/project")
		);
	}

	public function testAction()
	{
		$this->assertEquals(
			array(
				"controller" => "project",
				"action" => "stats"
			),
			Router::handle("/project/stats")
		);
	}

	public function testId()
	{
		$this->assertEquals(
			array(
				"controller" => "project",
				"id" => 42,
				"action" => "index"
			),
			Router::handle("/project/42")
		);
	}

	public function testIdAndAction()
	{
		$this->assertEquals(
			array(
				"controller" => "project",
				"id" => 42,
				"action" => "stats"
			),
			Router::handle("/project/42/stats")
		);
	}
}