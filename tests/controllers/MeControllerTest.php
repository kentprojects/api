<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class MeControllerTest extends KentProjects_Controller_TestBase
{
	/**
	 * @return stdClass
	 */
	public function testGetConvener()
	{
		$userTokenString = "convener";

		$request = $this->createSignedRequest(Request::GET, array(), $userTokenString);
		$response = new Response($request);
		$user = $this->getUserForToken($userTokenString)->getUser();

		$this->runController($request, $response, "Me");
		$me = json_decode($response->body());

		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("user", $me, "No ME user.");
		$this->assertObjectHasAttribute("settings", $me, "No ME settings.");
		$this->assertObjectHasAttribute("id", $me->user, "No ME user ID.");
		$this->assertEquals($me->user->id, $user->getId(), "Failed asserting the User ID was correct.");
	}

	/**
	 * @return stdClass
	 */
	public function testGetSupervisor()
	{
		$userTokenString = "supervisor";

		$request = $this->createSignedRequest(Request::GET, array(), $userTokenString);
		$response = new Response($request);
		$user = $this->getUserForToken($userTokenString)->getUser();

		$this->runController($request, $response, "Me");
		$me = json_decode($response->body());

		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("user", $me, "No ME user.");
		$this->assertObjectHasAttribute("settings", $me, "No ME settings.");
		$this->assertObjectHasAttribute("id", $me->user, "No ME user ID.");
		$this->assertEquals($me->user->id, $user->getId(), "Failed asserting the User ID was correct.");
	}

	/**
	 * @return stdClass
	 */
	public function testGetStudent()
	{
		$userTokenString = "student";

		$request = $this->createSignedRequest(Request::GET, array(), $userTokenString);
		$response = new Response($request);
		$user = $this->getUserForToken($userTokenString)->getUser();

		$this->runController($request, $response, "Me");
		$me = json_decode($response->body());

		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("user", $me, "No ME user.");
		$this->assertObjectHasAttribute("group", $me, "No ME group.");
		$this->assertObjectHasAttribute("project", $me, "No ME project.");
		$this->assertObjectHasAttribute("settings", $me, "No ME settings.");
		$this->assertObjectHasAttribute("id", $me->user, "No ME user ID.");
		$this->assertEquals($me->user->id, $user->getId(), "Failed asserting the User ID was correct.");
	}

	/**
	 * @depends testGetStudent
	 * @return stdClass
	 */
	public function testUpdateName()
	{
		$first_name = "Jeff";
		$last_name = "Winger";

		$request = $this->createSignedRequest(Request::PUT, array("post" => array(
			"first_name" => $first_name,
			"last_name" => $last_name
		)), "student");
		$response = new Response($request);
		$user = $this->getUserForToken("student")->getUser();

		$this->runController($request, $response, "Me");
		$me = json_decode($response->body());

		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("user", $me, "No ME user.");
		$this->assertObjectHasAttribute("group", $me, "No ME group.");
		$this->assertObjectHasAttribute("project", $me, "No ME project.");
		$this->assertObjectHasAttribute("settings", $me, "No ME settings.");
		$this->assertObjectHasAttribute("id", $me->user, "No ME user ID.");
		$this->assertEquals($me->user->id, $user->getId(), "Failed asserting the User ID was correct.");
		$this->assertEquals($first_name, $user->getFirstName(), "Failed to update the user's first name.");
		$this->assertEquals($last_name, $user->getLastName(), "Failed to update the user's last name.");
		$this->assertEquals($first_name . " " . $last_name, $user->getName(), "Failed to update the user's name.");
	}
}