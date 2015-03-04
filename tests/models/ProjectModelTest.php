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
		$project = Model_Project::getById(6);
		$this->assertNotEmpty($project, "Project doesn't exist.");
		$this->assertEquals(6, $project->getId());
	}
}