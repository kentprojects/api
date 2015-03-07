<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Notification
{
	/**
	 * @param string $type
	 * @param Model_User $actor
	 * @param array $references
	 * @param array $targets
	 * @throws InvalidArgumentException
	 * @return boolean|string
	 */
	public static function queue($type, Model_User $actor, array $references, array $targets)
	{
		static::validate($type, $actor, $references, $targets);

		$parameters = array(
			"type" => $type,
			"actor_id" => $actor->getId(),
			"references" => array(),
			"targets" => array()
		);

		foreach ($references as $reference => $id)
		{
			$parameters["references"][$reference] = intval($id);
		}

		foreach ($targets as $target)
		{
			if ($target === "conveners")
			{
				$parameters["targets"][] = $target;
				continue;
			}

			$splitTarget = explode("/", $target);
			switch ($splitTarget[0])
			{
				case "group":
				case "project":
				case "user":
					$parameters["targets"][] = $target;
					break;
			}
		}

		if ($type === "test")
		{
			return json_encode($parameters);
		}

		if (config("environment") === "production")
		{
			$pipe = "/var/www/notifications-pipe";
		}
		else
		{
			$pipe = "/var/www/notifications-dev-pipe";
		}

		$fh = fopen($pipe, "a+b");
		flock($fh, LOCK_EX);
		fwrite($fh, json_encode($parameters) . PHP_EOL);
		fflush($fh);
		flock($fh, LOCK_UN);
		fclose($fh);

		return true;
	}

	/**
	 * @param $type
	 * @param Model_User $actor
	 * @param array $references
	 * @param array $targets
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public static function validate($type, Model_User $actor, array $references, array $targets)
	{
		if (($type !== "test") && !Model_Notification::isValidType($type))
		{
			throw new InvalidArgumentException("Unknown TYPE '{$type}' to create a notification with.");
		}
		if ($actor->getId() === null)
		{
			throw new InvalidArgumentException("This actor has no ID.");
		}

		$allowedReferenceKeys = array("group_id", "project_id", "user_id", "year");

		foreach ($references as $reference => $id)
		{
			if (!in_array($reference, $allowedReferenceKeys))
			{
				throw new InvalidArgumentException("Invalid reference '{$reference}'.");
			}
		}

		foreach ($targets as $target)
		{
			if ($target === "conveners")
			{
				continue;
			}

			$splitTarget = explode("/", $target);
			if (count($splitTarget) !== 2)
			{
				throw new InvalidArgumentException("Invalid target '{$target}' passed to Notification queue. Aborting.");
			}

			switch ($splitTarget[0])
			{
				case "group":
				case "project":
				case "user":
					break;
				default:
					throw new InvalidArgumentException(
						"Invalid target '{$target}' passed to Notification queue. Aborting."
					);
			}
		}
	}
}