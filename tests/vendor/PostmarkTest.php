<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class PostmarkTest extends KentProjects_TestBase
{
	public function testSend()
	{
		$mail = new Postmark;
		$mail->setTo("james.dryden@kentprojects.com", "KentProject Developers");
		$mail->setSubject("A Test Email");
		$mail->setBody(array(
			"Hello there,\n\n",
			"This be a simple test to test the email sending.\n\n",
			"Kind regards,\n",
			"A test bot."
		));
		$this->assertTrue($mail->send());
	}
}