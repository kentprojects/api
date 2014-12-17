<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class ProjectControllerTest extends KentProjects_Controller_TestBase
{
	public function testGetProjects()
	{
		if (USE_DATABASE_STUB)
		{
			$this->markTestIncomplete("At the moment, the controller tests require a live database connection.");
			return;
		}

		$request = $this->createSignedRequest(Request::GET);
		$response = new Response($request);

		$this->runController($request, $response, "Projects");

		$this->assertEquals(200, $response->status());
	}

	public function testGetProject()
	{
		$request = $this->createSignedRequest(Request::GET, array(), array(), array("id" => 2));
		$response = new Response($request);

		$this->runController($request, $response, "Project");

		$this->assertEquals(200, $response->status());
		$project = json_decode($response->body());
		$this->assertEquals(2, $project->id);
	}
}