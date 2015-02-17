<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Generic
 * This represents somebody who wishes to do a generic action.
 */
final class Intent_Generic extends Intent
{
	/**
	 * @param array $data
	 * @throws HttpStatusException
	 * @throws IntentException
	 * @return void
	 */
	public function create(array $data)
	{
		parent::create($data);

		$this->mergeData($data);
		$this->save();

		/**
		 * This is where one would mail out, or at least add to a queue!
		 */
		$mail = new Postmark;
		//$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
		$mail->setTo("matt.house@kentprojects.com", "Matt House");
		$mail->setSubject("New Generic Intent");
		$mail->setBody(array(
			"Hello there,\n",
			"There has been a new intent created:\n\n",
			json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT),
			"\n\n",
			"Kind regards,\n",
			"Your beloved API"
		));
		$mail->send();
	}

	/**
	 * @param array $data
	 * @throws HttpStatusException
	 * @throws IntentException
	 * @return void
	 */
	public function update(array $data)
	{
		parent::update($data);

		$this->mergeData($data);
		$this->save();

		/**
		 * This would be where one would get a notification confirming that's all good!
		 */
		$mail = new Postmark;
		//$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
		$mail->setTo("matt.house@kentprojects.com", "Matt House");
		$mail->setSubject("New Generic Intent");
		$mail->setBody(array(
			"Hello there,\n",
			"An intent has been updated:\n\n",
			json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT),
			"\n\n",
			"Kind regards,\n",
			"Your beloved API"
		));
		$mail->send();
	}
}