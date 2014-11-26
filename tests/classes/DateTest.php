<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class DateTest extends KentProjects_TestBase
{
	public function testPrettyStyle()
	{
		$this->assertEquals("Wednesday 26th November 2014", Date::format(Date::PRETTY, 1417026734));
	}

	public function testRelativeStyle()
	{
		$this->assertEquals("3 minutes ago", Date::format(Date::RELATIVE, strtotime("-3 minutes")));
	}

	public function testStandardStyle()
	{
		$this->assertEquals("26 Nov 2014", Date::format(Date::STANDARD, 1417026734));
	}

	public function testSlashesStyle()
	{
		$this->assertEquals("26/Nov/2014", Date::format(Date::SLASHES, 1417026734));
	}

	public function testTimestampStyle()
	{
		$this->assertEquals("2014-11-26 18:32:14", Date::format(Date::TIMESTAMP, 1417026734));
	}
}