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
		new Auth($request, $response, Auth::APP);
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
		new Auth($request, $response, Auth::APP);
	}

	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Missing signature.
	 */
	public function testMissingSignature()
	{
		$request = $this->createUnsignedRequest(
			Request::GET,
			array(
				"key" => "foo",
				"expires" => time() + 100
			)

		);
		$response = new Response($request);
		new Auth($request, $response, Auth::APP);
	}

	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Expired request.
	 */
	public function testExpiredRequest()
	{
		$request = $this->createUnsignedRequest(
			Request::GET,
			array(
				"key" => "foo",
				"expires" => time() - 100,
				"signature" => "bar"
			)

		);
		$response = new Response($request);
		new Auth($request, $response, Auth::APP);
	}

	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Invalid application.
	 */
	public function testInvalidApplication()
	{
		$request = $this->createSignedRequest(
			Request::GET,
			array(
				"key" => "foo",
			)
		);
		$response = new Response($request);
		new Auth($request, $response, Auth::APP);
	}

	/**
	 * @expectedException HttpStatusException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage Missing user token.
	 */
	public function testMissingUserToken()
	{
		$request = $this->createSignedRequest(
			Request::GET
		);
		$response = new Response($request);
		new Auth($request, $response, Auth::USER);
	}


}