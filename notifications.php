<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * The idea behind this script is one queues a call to this script and passes it a JSON object of parameters.
 *
 * $ php notifications.php
 *     {type:"user_wants_to_access_a_year", actor_id:22, references:{year:2014}, targets:["convener"]}
 * $ php notifications.php
 *     {type:"user_wants_to_join_a_group", actor_id: 4, references:{group_id: 6}, targets:["group/6", "user/2"]}
 *
 * This script will try to be clever and send a notification to as many people are possible, depending on the targets.
 * #NahThatAin'tMe
 */
require_once __DIR__ . "/functions.php";
Timing::start("notifications");
$parameters = array();

try
{
	if (empty($argv[1]))
	{
		throw new InvalidArgumentException("No parameters passed to the notification script.");
	}

	if ($argv[1] === "Hello")
	{
		Log::debug("Why hello, dear chap!");
		exit();
	}

	$parameters = json_decode($argv[1], true);
	if (empty($parameters))
	{
		throw new InvalidArgumentException("Invalid JSON passed to the notification script.");
	}

	if (empty($parameters["type"]))
	{
		throw new InvalidArgumentException("No type parameter passed to the notification script.");
	}
	elseif (empty($parameters["actor_id"]))
	{
		throw new InvalidArgumentException("No actor_id parameter passed to the notification script.");
	}
	elseif (empty($parameters["references"]))
	{
		$parameters["references"] = array();
	}
	elseif (empty($parameters["targets"]))
	{
		throw new InvalidArgumentException("No targets parameter passed to the notification script.");
	}

	$actor = Model_User::getById($parameters["actor_id"]);
	if (empty($actor))
	{
		throw new InvalidArgumentException("Actor not found for notification script. Aborting.");
	}

	Notification::validate($parameters["type"], $actor, $parameters["references"], $parameters["targets"]);

	$notification = new Model_Notification($parameters["type"], $actor);

	if (!empty($parameters["references"]["group_id"]))
	{
		$group = Model_Group::getById($parameters["references"]["group_id"]);
		if (empty($group))
		{
			throw new InvalidArgumentException("Group not found for notification script. Aborting.");
		}
		$notification->setGroup($group);
	}

	if (!empty($parameters["references"]["intent_id"]))
	{
		$intent = Model_Intent::getById($parameters["references"]["intent_id"]);
		if (empty($intent))
		{
			throw new InvalidArgumentException("Intent not found for notification script. Aborting.");
		}
		$notification->setIntent($intent);
	}

	if (!empty($parameters["references"]["project_id"]))
	{
		$project = Model_Project::getById($parameters["references"]["project_id"]);
		if (empty($project))
		{
			throw new InvalidArgumentException("Project not found for notification script. Aborting.");
		}
		$notification->setProject($project);
	}

	if (!empty($parameters["references"]["user_id"]))
	{
		$user = Model_User::getById($parameters["references"]["user_id"]);
		if (empty($user))
		{
			throw new InvalidArgumentException("User not found for notification script. Aborting.");
		}
		$notification->setUser($user);
	}

	if (!empty($parameters["references"]["year"]))
	{
		$year = Model_Year::getById($parameters["references"]["year"]);
		if (empty($year))
		{
			throw new InvalidArgumentException("Year not found for notification script. Aborting.");
		}
		$notification->setYear($year);
	}

	$targetIds = array();
	foreach ($parameters["targets"] as $target)
	{
		if ($target === "conveners")
		{
			$year = Model_Year::getCurrentYear();
			foreach ($year->getConveners() as $convener)
			{
				$targetIds[] = $convener->getId();
			}
			continue;
		}

		$splitTarget = explode("/", $target);

		switch ($splitTarget[0])
		{
			case "group":
				$group = Model_Group::getById($splitTarget[1]);
				foreach ($group->getStudents() as $student)
				{
					/** @var Model_User $student */
					$targetIds[] = $student->getId();
				}
				break;
			case "project":
				$project = Model_Project::getById($splitTarget[1]);
				$targetIds[] = $project->getSupervisor()->getId();
				foreach ($project->getGroup()->getStudents() as $student)
				{
					/** @var Model_User $student */
					$targetIds[] = $student->getId();
				}
				break;
			case "user":
				$user = Model_User::getById($splitTarget[1]);
				$targetIds[] = $user->getId();
				break;
			default:
				throw new InvalidArgumentException(
					"Invalid target '{$target}' passed to notification script switch. Aborting."
				);
		}
	}

	$targetIds = array_unique($targetIds, SORT_NUMERIC);

	$notification->save();
	Log::debug($notification, $targetIds);
	Model_Notification::addTargets($notification, $targetIds);

	Timing::stop("notifications");
	if (config("environment") === "development")
	{
		Log::debug($parameters, Timing::export());
	}
	Log::write();
	exit();
}
catch (Exception $e)
{
	$error = array(
		"error" => true,
		"exception" => get_class($e),
		"message" => $e->getMessage()
	);
	$status = 500;

	switch (get_class($e))
	{
		case "DatabaseException":
			/** @var DatabaseException $e */
			if (config("environment") === "development")
			{
				$error["query"] = $e->getQuery();
				$error["types"] = $e->getTypes();
				$error["params"] = $e->getParams();
			}
			break;
		case "HttpStatusException":
			/** @var HttpStatusException $e */
			$error["status"] = $status = $e->getCode();
			break;
	}
	/** @var Exception $e */

	if (true)
	{
		$error["trace"] = explode("\n", $e->getTraceAsString());
	}

	Log::error($error, $parameters);
	Log::write();
	exit(1);
}