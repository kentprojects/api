<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class NotificationTest extends KentProjects_Controller_TestBase
{
	protected $references = array(
		"group_id" => 1,
		"project_id" => 1,
		"user_id" => 4,
		"year" => 2014
	);
	protected $targets = array(
		"conveners",
		"group/1",
		"user/2"
	);

	public function testQueuing()
	{
		$json = json_decode(Notification::queue("test", Model_User::getById(4), $this->references, $this->targets));
		$this->assertObjectHasAttribute("type", $json);
		$this->assertObjectHasAttribute("actor_id", $json);
		$this->assertObjectHasAttribute("targets", $json);
		$this->assertTrue(true);
	}
}