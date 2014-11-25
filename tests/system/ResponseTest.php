<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class ResponseTest extends KentProjects_TestBase
{
	public function testBody()
	{
		$body = "hello kitty!";

		$response = new Response;
		$response->body($body);
		$this->assertEquals($body, $response->body());
	}

	public function testHeader()
	{
		$response = new Response;

		$response->header("Content-Type", "application/json");
		$this->assertEquals("application/json", $response->header("Content-Type"));

		$response->headers(array("Accept-Type" => "text/xml"));
		$this->assertEquals("text/xml", $response->header("Accept-Type"));
	}

	public function testStatus()
	{
		$status = 200;

		$response = new Response;
		$response->status($status);
		$this->assertEquals($status, $response->status());
	}
}