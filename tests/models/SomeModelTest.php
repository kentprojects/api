<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class SomeModelTest extends KentProjects_Model_TestBase
{
	public function testSomething()
	{
		$project = Model_Project::getById(3);
		$this->assertNotEmpty($project, "Project doesn't exist.");
		$this->assertEquals(3, $project->getId());
	}
}