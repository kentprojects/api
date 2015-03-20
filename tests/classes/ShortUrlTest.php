<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class ShortUrlTest extends KentProjects_TestBase
{
	public function testEncode()
	{
		$this->assertEquals("Cku", ShortUrl::encode(122234));
		$this->assertEquals("2v5n1J", ShortUrl::encode(985386286));
		$this->assertEquals("3JNbJ", ShortUrl::encode(30983062));
	}

	public function testDecode()
	{
		$this->assertEquals(122234, ShortUrl::decode("Cku"));
		$this->assertEquals(985386286, ShortUrl::decode("2v5n1J"));
		$this->assertEquals(30983062, ShortUrl::decode("3JNbJ"));
	}
}