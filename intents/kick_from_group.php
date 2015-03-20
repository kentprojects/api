<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Kick_From_Group
 * Represents the group admin kicking a group member.
 */
final class Intent_Kick_From_Group extends Intent
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

		$students = $group->getStudents();

		if ($students->get($user->getId()) === null)
		{
			throw new HttpStatusException(404, "Student with `user_id` is not in this group.");
		}

		$students->remove($user);
		$students->save();

		$acl = new ACL($user);
		$acl->delete("group/" . $group->getId());
		$acl->set("group", true, true, false, false);
		$acl->save();

		Notification::queue(
			"user_kicked_from_group", $actor,
			array(
				"group_id" => $group->getId(),
				"user_id" => $user->getId()
			),
			array(
				"group/" . $group->getId(),
				"user/" . $user->getId()
			)
		);
	}
}