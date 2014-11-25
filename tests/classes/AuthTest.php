<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class AuthTest extends KentProjects_Controller_TestBase
{
	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Missing application key.
	 */
	public function testMissingApplicationKey()
	{
		$request = $this->createUnsignedRequest(Request::GET);
		$response = new Response($request);
		$this->runController($request, $response, "Year");
	}

	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Missing expiry timestamp.
	 */
	public function testMissingExpiryTimestamp()
	{
		$request = $this->createUnsignedRequest(
			Request::GET,
			array(
				"key" => "foo"
			)
		);
		$response = new Response($request);
		$this->runController($request, $response, "Year");
	}
}