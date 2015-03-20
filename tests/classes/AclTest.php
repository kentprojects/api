<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class AclTest extends KentProjects_TestBase
{
	/**
	 * @return ACL
	 */
	public function testGetACLs()
	{
		$user = Model_User::getById(1);
		$this->assertNotEmpty($user, "Failed to get a user.");
		$ACL = new ACL($user);
		$this->assertGreaterThan(0, count($ACL), "Failed to get any ACLs for this user.");
		return $ACL;
	}

	/**
	 * @depends testGetACLs
	 *
	 * @param ACL $ACL
	 * @return void
	 */
	public function testSaveACLs(ACL $ACL)
	{
		$ACL->save();
	}

	/**
	 * @depends testGetACLs
	 *
	 * @param ACL $ACL
	 * @return array
	 */
	public function testSetIndividualACLs(ACL $ACL)
	{
		$key = "test/" . uniqid();
		$values = array("create" => false, "read" => true, "update" => false, "delete" => false);

		call_user_func_array(array($ACL, "set"), array_merge(array($key), array_values($values)));
		$ACL->save();
		$ACL->fetch();

		$this->assertEquals($values, $ACL->get($key));

		return array($ACL, $key);
	}

	/**
	 * @depends testGetACLs
	 *
	 * @param ACL $ACL
	 * @return void
	 */
	public function testSetGlobalACLs(ACL $ACL)
	{
		$key = "test-global";
		$values = array("create" => false, "read" => true, "update" => false, "delete" => false);

		call_user_func_array(array($ACL, "set"), array_merge(array($key), array_values($values)));
		$ACL->save();
		$ACL->fetch();

		$this->assertEquals($values, $ACL->get($key . "/" . uniqid()));
	}

	/**
	 * @depends testSetIndividualACLs
	 *
	 * @param array $data
	 * @return void
	 */
	public function testDeleteACL(array $data)
	{
		/**
		 * @var ACL $ACL
		 * @var string $key
		 */
		list($ACL, $key) = $data;

		$ACL->delete($key);
		$ACL->save();
		$ACL->fetch();

		$this->assertEquals(
			array("create" => false, "read" => false, "update" => false, "delete" => false), $ACL->get($key)
		);
	}
}