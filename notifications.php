<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * The idea behind this script is one queues a call to this script and passes it a JSON object of parameters.
 *
 * $ php notifications.php {type:"user_wants_to_access_a_year", actor_id:22, year:2014, targets: ["convener"]}
 * $ php notifications.php
 *     {type:"user_wants_to_join_a_group", actor_id: 4, group_id: 6, targets: ["group/6", "user/2"]}
 *
 * This script will try to be clever and send a notification to as many people are possible, depending on the targets.
 * #NahThatAin'tMe
 */
require_once __DIR__ . "/functions.php";
Timing::start("notifications");
$_GET = array();

try
{
	if (empty($argv[1]))
	{
		throw new InvalidArgumentException("No parameters passed to the notification script.");
	}

	$data = json_decode($argv[1]);
	if (empty($data))
	{
		throw new InvalidArgumentException("Invalid JSON passed to the notification script.");
	}

	if (empty($data->type))
	{
		throw new InvalidArgumentException("No type parameter passed to the notification script.");
	}
	elseif (empty($data->actor_id))
	{
		throw new InvalidArgumentException("No actor_id parameter passed to the notification script.");
	}
	elseif (empty($data->targets))
	{
		throw new InvalidArgumentException("No targets parameter passed to the notification script.");
	}

	$actor = Model_User::getById($data->actor_id);
	if (empty($actor))
	{
		throw new InvalidArgumentException("Actor not found for notification script. Aborting.");
	}

	$notification = new Model_Notification($data->type, $actor);

	if (!empty($data->group_id))
	{
		$group = Model_Group::getById($data->group_id);
		if (empty($group))
		{
			throw new InvalidArgumentException("Group not found for notification script. Aborting.");
		}
		$notification->setGroup($group);
	}

	if (!empty($data->project_id))
	{
		$project = Model_Project::getById($data->project_id);
		if (empty($project))
		{
			throw new InvalidArgumentException("Project not found for notification script. Aborting.");
		}
		$notification->setProject($project);
	}

	if (!empty($data->user_id))
	{
		$user = Model_User::getById($data->user_id);
		if (empty($user))
		{
			throw new InvalidArgumentException("User not found for notification script. Aborting.");
		}
		$notification->setUser($user);
	}

	if (!empty($data->year))
	{
		$year = Model_Year::getById($data->year);
		if (empty($year))
		{
			throw new InvalidArgumentException("Year not found for notification script. Aborting.");
		}
		$notification->setYear($year);
	}

	$targetIds = array();
	/**
	 * This foreach is identical to Notification::queue and SHOULD STAY THAT WAY!
	 */
	foreach ($data->targets as $target)
	{
		$splitTarget = explode("/", $target);
		if (count($splitTarget) !== 2)
		{
			throw new InvalidArgumentException("Invalid target '{$target}' passed to notification script. Aborting.");
		}

		switch ($splitTarget[0])
		{
			case "group":
				$group = Model_Group::getById($splitTarget[1]);
				break;
			case "project":
				$project = Model_Project::getById($splitTarget[1]);
				break;
			case "user":
				$user = Model_User::getById($splitTarget[1]);
				break;
			default:
				throw new InvalidArgumentException(
					"Invalid target '{$target}' passed to notification script switch. Aborting."
				);
		}
	}
}
catch (Exception $e)
{
	Log::error($e->getMessage(), $_GET);
}

Timing::stop("notifications");
if (config("environment") === "development")
{
	Log::debug($_GET, Timing::export());
}
Log::write();