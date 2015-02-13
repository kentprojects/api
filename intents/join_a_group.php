<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Intent_Join_A_Group extends Intent
{
	/**
	 * This represents somebody who wishes to join a group.
	 *
	 * @param array $data
	 * @throws HttpStatusException
	 * @throws IntentException
	 * @return void
	 */
	public function create(array $data)
	{
		parent::create($data);

		if (empty($data["group_id"]))
		{
			throw new HttpStatusException(400, "Missing parameter 'group_id' for this intent.");
		}

		$group = Model_Group::getById($data["group_id"]);
		if (empty($group))
		{
			throw new IntentException("Invalid group_id passed to intent.");
		}

		$this->mergeData(array(
			"group_id" => $group->getId(),
			"state" => Intent::STATE_OPEN
		));
		$this->save();

		/**
		 * This is where one would mail out, or at least add to a queue!
		 */
		$mail = new Mail;
		$mail->setTo("developers@kentprojects.com", "KentProject Developers");
		$mail->setSubject("New INTENT");
		$mail->setBody(array(
			"Hello everyone,\n",
			"There has been a new intent created:\n\n",
			json_encode($this, JSON_PRETTY_PRINT),
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

		if (empty($data["action"]))
		{
			throw new HttpStatusException(400, "Missing parameter 'action' for this intent.");
		}

		$this->mergeData(array(
			"state" => Intent::STATE_REJECTED
		));
		$this->save();

		/**
		 * This would be where one would get a notification confirming that's all good!
		 */
	}
}