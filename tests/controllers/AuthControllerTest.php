<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class AuthControllerTest extends KentProjects_Controller_TestBase
{
	/**
	 * @throws HttpRedirectException
	 * @return void
	 */
	public function testInternalLogin()
	{
		$request = $this->createUnsignedRequest(
			Request::GET,
			array(
				"get" => array(
					"auth" => "f4dfeada0e91e1791a80da1bb26a7d96"
				)
			),
			"student"
		);
		$response = new Response($request);
		$success = $url = null;

		try
		{
			$this->runController($request, $response, "Auth", "internal");
		}
		catch (HttpRedirectException $e)
		{
			print_r($e->getLocation());
			if (strpos($e->getLocation(), "kentprojects.com/login.php?success=") === false)
			{
				throw $e;
			}

			$url = parse_url($e->getLocation(), PHP_URL_QUERY);
		}

		if (empty($url))
		{
			$this->fail("No URL was thrown from the internal login.");
		}

		parse_str($url);

		if (empty($success))
		{
			$this->fail("No SUCCESS query was found from the URL thrown from the internal login.");
		}

		$request = $this->createSignedRequest(
			Request::GET,
			array(
				"get" => array(
					"code" => $success
				)
			)
		);
		$response = new Response($request);

		$this->runController($request, $response, "Auth", "confirm");
		$this->assertEquals(200, $response->status());

		$token = json_decode($response->body());
	}
}