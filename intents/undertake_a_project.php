<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Undertake_Project
 * Represents a group wanting to undertake a project.
 */
final class Intent_Undertake_A_Project extends Intent
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
		$group = $user->getCurrentGroup();
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

		if (empty($this->data->project_id))
		{
			throw new IntentException("Missing project_id.");
		}

		$project = Model_Project::getById($this->data->project_id);
		if (empty($project))
		{
			throw new IntentException("Missing project.");
		}

		return $project->getSupervisor()->getId() == $user->getId();
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

		if (empty($data["project_id"]))
		{
			throw new HttpStatusException(400, "Missing parameter 'project_id' for this intent.");
		}

		$project = Model_Project::getById($data["project_id"]);
		if (empty($project))
		{
			throw new HttpStatusException(404, "Project with `project_id` is not found.");
		}

		$data = array_merge($data, array(
			"project_id" => $project->getId()
		));

		$this->deduplicate(array(
			"project_id" => $project->getId()
		));
		$this->mergeData($data);
		$this->save();

		$group_name = $this->model->getUser()->getCurrentGroup()->getName();
		$intent_creator_name = $this->model->getUser()->getName();
		$project_supervisor_name = $project->getSupervisor()->getFirstName();
		$project_name = $project->getName();

		$path = sprintf("intents.php?action=view&id=%d", $this->model->getId());

		$body = array(
			"Hey {$project_supervisor_name},\n\n",
			"{$intent_creator_name} and the group '{$group_name}' wishes to undertake your project '{$project_name}'.\n\n",
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
	 * @return array
	 */
	public function jsonSerialize()
	{
		$json = parent::jsonSerialize();
		$json["group"] = Model_Group::getById($this->data->group_id);
		return $json;
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

		if (empty($this->data->project_id))
		{
			throw new IntentException("Missing project_id.");
		}

		$project = Model_Project::getById($this->data->project_id);
		if (empty($project))
		{
			throw new IntentException("Missing project.");
		}

		$this->mergeData($data);
		$group = $this->model->getUser()->getCurrentGroup();

		$intent_creator_name = $this->model->getUser()->getName();
		$project_supervisor_name = $project->getSupervisor()->getFirstName();
		$project_name = $project->getName();

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
				$project->setGroup($group);
				$project->save();
				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$project_supervisor_name} was lovely and allowed you to undertake '{$project_name}'.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				$mail->send();
				break;
			case static::STATE_REJECTED:
				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"{$project_supervisor_name} was incredibly rude and has rejected your request to join '{$project_name}'.\n",
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