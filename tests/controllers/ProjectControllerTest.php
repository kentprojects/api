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
		$request = $this->createSignedRequest(Request::GET, array(), "convener");
		$response = new Response($request);

		$this->runController($request, $response, "Projects");
		$this->assertEquals(200, $response->status());
	}

	public function testGetProject()
	{
		$request = $this->createSignedRequest(
			Request::GET,
			array(
				"param" => array(
					"id" => 6
				)
			),
			"convener"
		);
		$response = new Response($request);

		$this->runController($request, $response, "Project");

		$this->assertEquals(200, $response->status());
		$project = json_decode($response->body());
		$this->assertEquals(6, $project->id);
	}

	/**
	 * @depends testGetProject
	 */
	public function testSetProjectDescription()
	{
		$description = "A fantastic chance to change the way a kettle is boiled! " . uniqid();
		$request = $this->createSignedRequest(
			Request::PUT,
			array(
				"post" => array(
					"description" => $description
				),
				"param" => array(
					"id" => 6
				)
			),
			"supervisor"
		);
		$response = new Response($request);

		$this->runController($request, $response, "Project");

		$this->assertEquals(200, $response->status());
		$project = json_decode($response->body());
		$this->assertEquals(6, $project->id);
		$this->assertEquals($description, $project->description);
	}
}