<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Intent_Submit_To_Cas
 * Represents a group with a project wanting to confirm this project with the correct authorities.
 */
final class Intent_Submit_To_Cas extends Intent
{
	/**
	 * Can this particular user create an intent of this kind?
	 *
	 * @param Model_User $user
	 * @return bool
	 */
	public function canCreate(Model_User $user)
	{
		if (!$user->isStudent())
		{
			return false;
		}

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
		$mail = new Postmark;

		$additionalInformation = !empty($data["additional"]);

		$data = array_merge($data, array(
			"additional" => $additionalInformation
		));

		$this->deduplicate(array(
			"submit_to_cas" => "submit_to_cas"
		));
		$this->mergeData(array_merge($data, array(
			"additional" => $additionalInformation
		)));
		$this->state(Intent::STATE_ACCEPTED);
		$this->save();

		$user = $this->model->getUser();
		$group = $user->getGroup();
		$project = $group->getProject();

		$body = array(
			"CO600 PROJECT ACCEPTANCE FORM 2014/2015\n\n----\n\n",
			"I/We, the aforementioned:\n\n"
		);
		foreach ($group->getStudents() as $student)
		{
			/** @var Model_User $student */
			$body[] = "- " . $student->getName() . " " . $student->getEmail();
		}
		array_push(
			$body,
			"\n\nWish to register for the CO600 project entitled:\n\n",
			$project->getName(),
			"\n\n----\n\nSupervisor Name: " . $project->getSupervisor()->getName() . "\n\n",
			"(1) I, the supervisor, have agreed to supervise the project for the students(s) named above and have made",
			"sure that any special resourced will be available for the start of the project.\n"
		);

		if ($additionalInformation)
		{
			array_push(
				$body,
				"(2) This project will entail research involving human participants as defined by the Faculty Research",
				"Ethics Procedures and as such this group has been notified that they are required to manually fill in",
				"a Project Acceptance form and submit that to the CAS office instead."
			);
		}
		else
		{
			array_push(
				$body,
				"(2) This project will not entail research involving human participants as defined by the Faculty",
				"Research Ethics Procedures."
			);
		}

		$body[] = "\n\n----\n\nKind regards,\nThe KentProjects API";

		if (config("environment") === "development")
		{
			$mail->setTo("james.dryden@kentprojects.com", "James Dryden");
			$mail->setTo("matt.house@kentprojects.com", "Matt House");
		}
		else
		{
			$mail->setTo("matt.house@kentprojects.com", "Matt House");
		}
		$mail->setSubject("New CO600 Project Acceptance form submitted.");
		$mail->setBody($body);
		$mail->send();
	}
}