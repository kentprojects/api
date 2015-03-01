<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Leave_A_Group
 * Represents someone wanting to leave a group.
 */
final class Intent_Leave_A_Group extends Intent
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
		if (count($groups) !== 1)
		{
			return false;
		}

		/**
		 * All okay!
		 */
		return true;
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
			throw new HttpStatusException(404, "Group with `group_id` is not found.");
		}

		$students = new GroupStudentMap($group);
		// TODO: This should be a HAS and take an existing Model.
		$student = $students->get($this->model->getUser()->getId());
		if (empty($student))
		{
			throw new HttpStatusException(400, "You are not in this group.");
		}
		$students->remove($this->model->getUser());
		$students->save();

		// TODO: Remove the original intent to join too.

		$acl = new ACL($this->model->getUser());
		$acl->delete("group/" . $group->getId());
		$acl->set("group", true, true, false, false);
		$acl->save();

		$this->model->getUser()->clearCaches();
	}
}