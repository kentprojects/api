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
						"user/" . $this->model->getUser()->getId()
					)
				);
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
				break;
			default:
				throw new IntentException("This state is not a valid Intent STATE constant.");
		}

		$this->save();
	}
}