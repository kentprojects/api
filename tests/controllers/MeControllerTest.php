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
		$this->runController($request, $response, "Me");

		$me = json_decode($response->body());
		static::$tokenModels["student"] = null;
		$user = $this->getUserForToken("student")->getUser();

		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("user", $me, "No ME user.");
		$this->assertObjectHasAttribute("group", $me, "No ME group.");
		$this->assertObjectHasAttribute("project", $me, "No ME project.");
		$this->assertObjectHasAttribute("settings", $me, "No ME settings.");
		$this->assertObjectHasAttribute("id", $me->user, "No ME user ID.");
		$this->assertEquals($user->getId(), $me->user->id, "Failed asserting the User ID was correct.");
		$this->assertEquals($user->getFirstName(), $first_name, "Failed to update the user's first name.");
		$this->assertEquals($user->getLastName(), $last_name, "Failed to update the user's last name.");
		$this->assertEquals($user->getName(), $first_name . " " . $last_name, "Failed to update the user's name.");
	}
}