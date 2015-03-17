<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Invite_To_Group
 * Represents someone wanting someone else to join a group.
 */
final class Intent_Invite_To_Group extends Intent
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
		 * If you don't have a group.
		 */
		$group = $user->getGroup();
		if (empty($group))
		{
			return false;
		}

		/**
		 * Are you the creator ID?
		 */
		return ($group->getCreator()->getId() == $user->getId());
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

		/**
		 * If you are not a student.
		 */
		if (!$user->isStudent())
		{
			return false;
		}

		/**
		 * If you are in a group already, then fail.
		 */
		$group = Model_Group::getByUser($user);
		return empty($group);
	}

	/**
	 * This represents somebody who wishes to join a group.
	 *
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

		$user = Model_Student::getById($data["user_id"]);
		if (empty($user))
		{
			throw new HttpStatusException(404, "Student with `user_id` is not found.");
		}

		$group = $actor->getGroup();

		$this->deduplicate(array(
			"group_id" => $group->getId(),
			"user_id" => $user->getId()
		));
		$this->mergeData(array_merge($data, array(
			"group_id" => $group->getId(),
			"user_id" => $user->getId()
		)));
		$this->save();

		Notification::queue(
			"user_wants_another_to_join_a_group", $this->model->getUser(),
			array(
				"group_id" => $group->getId(),
				"intent_id" => $this->getId(),
				"user_id" => $user->getId()
			),
			array(
				"group/" . $group->getId(),
				"user/" . $user->getId()
			)
		);
	}

	/**
	 * @param Request_Internal $request
	 * @param Response $response
	 * @param ACL $acl
	 * @param boolean $internal
	 * @throws HttpStatusException
	 * @return array
	 */
	public function render(Request_Internal $request, Response &$response, ACL $acl, $internal = false)
	{
		$groupId = $this->data->group_id;
		if (empty($groupId))
		{
			throw new HttpStatusException(500, "Failed to fetch group ID for this intent.");
		}
		$group = Model_Group::getById($groupId);
		if (empty($group))
		{
			throw new HttpStatusException(500, "Failed to fetch group for this intent.");
		}

		$userId = $this->data->user_id;
		if (empty($userId))
		{
			throw new HttpStatusException(500, "Failed to fetch student ID for this intent.");
		}
		$user = Model_Student::getById($userId);
		if (empty($user))
		{
			throw new HttpStatusException(500, "Failed to fetch student for this intent.");
		}

		$rendered = parent::render($request, $response, $acl, $internal);
		$rendered["group"] = $group->render($request, $response, $acl, true);
		$rendered["user"] = $user->render($request, $response, $acl, true);
		return $rendered;
	}

	/**
	 * @param array $data
	 * @param Model_User $actor
	 * @throws IntentException
	 */
	public function update(array $data, Model_User $actor)
	{
		parent::update($data, $actor);

		if (empty($this->data->group_id))
		{
			throw new IntentException("Missing group_id.");
		}

		$groupId = $this->data->group_id;
		if (empty($groupId))
		{
			throw new IntentException("Failed to fetch group ID for this intent.");
		}
		$group = Model_Group::getById($groupId);
		if (empty($group))
		{
			throw new IntentException("Failed to fetch group for this intent.");
		}

		$userId = $this->data->user_id;
		if (empty($userId))
		{
			throw new IntentException("Failed to fetch student ID for this intent.");
		}
		$user = Model_Student::getById($userId);
		if (empty($user))
		{
			throw new IntentException("Failed to fetch student for this intent.");
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

				$acl = new ACL($this->model->getUser());
				$acl->set("group", false, true, false, false);
				$acl->set("group/" . $group->getId(), false, true, true, true);
				$acl->save();

				/**
				 * Since only the group creator can manage this stuff, we can be sure the group creator is the ACTOR
				 * for this notification.
				 */
				Notification::queue(
					"user_accepted_invite_to_join_a_group", $actor,
					array(
						"group_id" => $group->getId()
					),
					array(
						"group/" . $group->getId()
					)
				);

				$this->model->getUser()->clearCaches();

				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$group_creator_name} was a total lad and allowed you to join '{$group_name}'.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				// $mail->send();
				break;
			case static::STATE_REJECTED:
				Notification::queue(
					"user_declined_invite_to_join_a_group", $actor,
					array(
						"group_id" => $group->getId()
					),
					array(
						"group/" . $group->getId()
					)
				);

				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$group_creator_name} was a total dick and has rejected your request to join '{$group_name}'.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				// $mail->send();
				break;
			default:
				throw new IntentException("This state is not a valid Intent STATE constant.");
		}

		$this->save();
	}
}