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
	 * @param Model_Intent $model
	 * @throws IntentException
	 */
	public function __construct(Model_Intent $model)
	{
		if (config("environment") !== "development")
		{
			throw new IntentException("Generic intents are only available to the development environment.");
		}

		parent::__construct($model);
	}

	/**
	 * @param Model_User $user
	 * @return bool
	 */
	public function canUpdate(Model_User $user)
	{
		return ($this->data->user_id == $user->getId());
	}

	/**
	 * @param array $data
	 * @param Model_User $actor
	 * @throws HttpStatusException
	 * @throws IntentException
	 */
	public function create(array $data, Model_User $actor)
	{
		parent::create($data, $actor);

		if (empty($data["user_id"]))
		{
			throw new HttpStatusException(400, "Missing parameter 'user_id' for this intent.");
		}

		$user = Model_User::getById($data["user_id"]);
		if (empty($user))
		{
			throw new IntentException("Invalid user_id passed to intent.");
		}

		$this->mergeData(array_merge($data, array(
			"user_id" => $user->getId()
		)));
		$this->save();

		Notification::queue(
			"user_got_a_notification", $this->model->getUser(),
			array(), array("user/" . $this->model->getUser()->getId())
		);

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
	 * @return array
	 */
	public function jsonSerialize()
	{
		$json = parent::jsonSerialize();
		$json["user"] = Model_User::getById($json["data"]["user_id"]);
		return $json;
	}

	/**
	 * @param array $data
	 * @param Model_User $actor
	 * @throws IntentException
	 */
	public function update(array $data, Model_User $actor)
	{
		parent::update($data, $actor);

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