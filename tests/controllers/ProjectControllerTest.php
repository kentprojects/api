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
		$request = $this->createSignedRequest(Request::GET);
		$response = new Response($request);

		$this->runController($request, $response, "Projects");

		$this->assertEquals(200, $response->status());
	}

	public function testGetProject()
	{
		$request = $this->createSignedRequest(Request::GET, array(), array(), array("id" => 22));
		$response = new Response($request);

		$this->runController($request, $response, "Project");

		$this->assertEquals(200, $response->status());
	}
}