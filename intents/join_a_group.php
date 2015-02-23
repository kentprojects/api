<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Join_A_Group
 * Represents someone wanting to join a group.
 */
final class Intent_Join_A_Group extends Intent
{
	/**
	 * Can this particular user create an intent of this kind?
	 *
	 * @param Model_User $user
	 * @return bool
	 */
	public function canCreate(Model_User $user)
	{
		/**
		 * If you are not a student.
		 */
		if (!$user->isStudent())
		{
			return false;
		}

		/**
		 * If you are in a group already.
		 */
		$groups = new StudentGroupMap($user);
		if (count($groups) > 0)
		{
			return false;
		}

		/**
		 * All okay!
		 */
		return true;
	}

	/**
	 * Can this particular user update this intent?
	 * In particular, is this user the creator of the group?
	 *
	 * @param Model_User $user
	 * @throws IntentException
	 * @return bool
	 */
	public function canUpdate(Model_User $user)
	{
		if (parent::canUpdate($user) === true)
		{
			return true;
		}

		if (empty($this->data->group_id))
		{
			throw new IntentException("Missing group_id.");
		}

		$group = Model_Group::getById($this->data->group_id);
		if (empty($group))
		{
			throw new IntentException("Missing group.");
		}

		return $group->getCreator()->getId() == $user->getId();
	}

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
			"group_id" => $group->getId()
		));
		$this->save();

		$group_creator_name = $group->getCreator()->getFirstName();
		$group_name = $group->getName();
		$intent_creator_name = $this->model->getUser()->getName();

		$path = sprintf("intents.php?action=view&id=%d", $this->model->getId());

		$body = array(
			"Hey {$group_creator_name},\n\n",
			"{$intent_creator_name} wishes to join your group '{$group_name}'.\n\n",
			"To accept, please click on the relevant link:\n\n",
			"> http://localhost:5757/{$path}\n",
			"> http://localhost:8080/{$path}\n",
			"> http://dev.kentprojects.com/{$path}\n",
			"> http://kentprojects.com/{$path}\n\n",
			"Kind regards,\n",
			"Your awesome API\n\n\n",
			"For reference, here's the JSON export of the intent:\n",
			json_encode($this, JSON_PRETTY_PRINT)
		);

		/**
		 * This is where one would mail out, or at least add to a queue!
		 */
		$mail = new Postmark;
		$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
		$mail->setTo("matt.house@kentprojects.com", "Matt House");
		$mail->setSubject("New Intent #" . $this->model->getId());
		$mail->setBody($body);
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

		if (empty($this->data->group_id))
		{
			throw new IntentException("Missing group_id.");
		}

		$group = Model_Group::getById($this->data->group_id);
		if (empty($group))
		{
			throw new IntentException("Missing group.");
		}

		$this->mergeData($data);

		$group_creator_name = $group->getCreator()->getFirstName();
		$group_name = $group->getName();
		$intent_creator_name = $this->model->getUser()->getName();

		$mail = new Postmark;
		$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
		$mail->setTo("matt.house@kentprojects.com", "Matt House");
		$mail->setSubject("Update Intent #" . $this->model->getId());

		switch ($this->state())
		{
			case static::STATE_OPEN:
				/**
				 * This is not the state you are looking for. Move along.
				 */
				return;
			case static::STATE_ACCEPTED:
				$students = new GroupStudentMap($group);
				$students->add($this->model->getUser());
				$students->save();
				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$group_creator_name} was a total lad and allowed you to join '{$group_name}'.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				$mail->send();
				break;
			case static::STATE_REJECTED:
				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$group_creator_name} was a total dick and has rejected your request to join '{$group_name}'.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				$mail->send();
				break;
			default:
				throw new IntentException("This state is not a valid Intent STATE constant.");
		}

		$this->save();
	}
}