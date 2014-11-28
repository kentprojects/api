<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class RequestTest extends KentProjects_TestBase
{
	public function testExternalRequestFetch()
	{
		$request = Request::factory(Request::GET, "http://kentprojects.com");
		$this->assertEquals("Request_External", get_class($request));
	}

	public function testInternalRequestFetch()
	{
		$request = Request::factory(Request::GET, "/user/22");
		$this->assertEquals("Request_Internal", get_class($request));
	}

	/**
	 * @expectedException Exception
	 */
	public function testMalformedUrlRequestFetch()
	{
		Request::factory(Request::GET, "");
	}
}