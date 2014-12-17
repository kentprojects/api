<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class ProjectModelTest extends KentProjects_Model_TestBase
{
	public function testGetById()
	{
		$project = Model_Project::getById(2);
		$this->assertNotEmpty($project, "Project doesn't exist.");
		$this->assertEquals(2, $project->getId());
	}
}