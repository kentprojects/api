<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Access_Year
 * Represents someone wanted access to the current year.
 */
final class Intent_Access_Year extends Intent
{
	/**
	 * Can this particular user create an intent of this kind?
	 * In particular, is this user "enrolled" on the current year?
	 *
	 * @param Model_User $user
	 * @return bool
	 */
	public function canCreate(Model_User $user)
	{
		$years = new UserYearMap($user);
		if (count($years) > 0)
		{
			$currentYear = $years->getCurrentYear();
			return !empty($currentYear);
		}

		return true;
	}

	/**
	 * Can this particular user update this intent?
	 * In particular, is this user a convener?
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

		$currentYear = Model_Year::getCurrentYear();
		$conveners = $currentYear->getConveners();
		foreach ($conveners as $convener)
		{
			if ($convener->getId() == $user->getId())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * This represents somebody who wishes to join the current year.
	 *
	 * @param array $data
	 * @param Model_User $actor
	 * @throws HttpStatusException
	 * @throws IntentException
	 */
	public function create(array $data, Model_User $actor)
	{
		parent::create($data, $actor);

		$currentYear = Model_Year::getCurrentYear();
		$this->deduplicate(array(
			"year" => $currentYear->getId()
		));
		$this->mergeData($data);
		$this->save();

		Notification::queue(
			"user_wants_to_access_a_year", $actor,
			array(
				"intent_id" => $this->getId(),
				"year" => (string)$currentYear
			),
			array("conveners")
		);

		$intent_creator_name = $actor->getName();
		$path = sprintf("intents.php?action=view&id=%d", $this->model->getId());

		$body = array(
			"Hey there,\n\n",
			"{$intent_creator_name} wishes to access the platform this year.\n\n",
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
		// $mail->send();
	}

	/**
	 * @param array $data
	 * @param Model_User $actor
	 * @throws CacheException
	 * @throws IntentException
	 */
	public function update(array $data, Model_User $actor)
	{
		parent::update($data, $actor);

		$intentAuthor = $this->model->getUser();

		$this->mergeData($data);
		$intent_creator_name = $intentAuthor->getName();

		$roles = array();
		if ($intentAuthor->isStaff())
		{
			foreach (array("role_convener", "role_supervisor", "role_secondmarker") as $role)
			{
				if (!empty($data[$role]) && ($data[$role] === true))
				{
					$roles[$role] = true;
				}
			}
		}

		$currentYear = Model_Year::getCurrentYear();

		$mail = new Postmark;
		$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
		$mail->setTo("matt.house@kentprojects.com", "Matt House");
		$mail->setSubject("Update Intent #" . $this->model->getId());

		switch ($this->state())
		{
			case static::STATE_OPEN:
				return;
			case static::STATE_ACCEPTED:
				$years = new UserYearMap($intentAuthor);
				$years->add($currentYear, $roles);
				$years->save();
				Cache::delete($intentAuthor->getCacheName());

				Notification::queue(
					"user_approved_access_to_year", $actor,
					array(
						"user_id" => $this->model->getUser(),
						"year" => (string)$currentYear
					),
					array(
						"conveners",
						"user/" . $this->model->getUser()
					)
				);

				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"You have been granted access to the year.\n",
					"Get going!\n\n",
					"Kind regards,\n",
					"Your awesome API"
				));
				// $mail->send();
				break;
			case static::STATE_REJECTED:
				Notification::queue(
					"user_rejected_access_to_year", $actor,
					array(
						"user_id" => $this->model->getUser(),
						"year" => (string)$currentYear
					),
					array(
						"conveners",
						"user/" . $this->model->getUser()
					)
				);

				$mail->setBody(array(
					"Hey {$intent_creator_name},\n\n",
					"You have been declined access to the year.\n",
					"Sorry about that!\n\n",
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