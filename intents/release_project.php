<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Release_Project
 * Represents a group wanting to release a project they've undertaken.
 */
final class Intent_Release_Project extends Intent
{
	/**
	 * Can this particular user create an intent of this kind?
	 *
	 * @param Model_User $user
	 * @return bool
	 */
	public function canCreate(Model_User $user)
	{
		if ($user->isStudent())
		{
			$group = $user->getGroup();
			if (empty($group))
			{
				return false;
			}
			if (!$group->hasProject())
			{
				return false;
			}
			return ($group->getCreator()->getId() == $user->getId());
		}
		else
		{
			$years = $user->getYears();
			$currentYear = $years->getCurrentYear();
			return ($currentYear->role_supervisor == 1);
		}
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

		if (empty($data["project_id"]))
		{
			throw new HttpStatusException(400, "Missing parameter 'project_id' for this intent.");
		}

		$project = Model_Project::getById($data["project_id"]);
		if (empty($project))
		{
			throw new HttpStatusException(404, "Project with `project_id` is not found.");
		}
		if (!$project->hasGroup())
		{
			throw new HttpStatusException(400, "This project doesn't have a group attached to it.");
		}

		$group = $project->getGroup();
		$user = $this->model->getUser();

		if ($user->isStudent())
		{
			if ($group->getId() != $user->getGroup()->getId())
			{
				throw (new HttpStatusException(400, "Your group has not undertaken this project."))->setData(array(
					"project_id" => $project->getId(),
					"project_group_id" => $group->getId(),
					"user_group_id" => $user->getGroup()->getId()
				));
			}
		}
		else
		{
			if ($project->getSupervisor()->getId() != $user->getId())
			{
				throw new HttpStatusException(400, "This supervisor is not the supervisor of this project.");
			}
		}

		$project->removeGroup();
		$project->save();

		Notification::queue(
			"group_released_project", $this->model->getUser(),
			array("group_id" => $group->getId()),
			array(
				"group/" . $group->getId(),
				"user/" . $project->getSupervisor()->getId()
			)
		);

		$this->deduplicateClear("undertake_a_project", array(
			"project_id" => $project->getId()
		));

		$group->clearCaches();
		$project->clearCaches();
		$user->clearCaches();
	}
}