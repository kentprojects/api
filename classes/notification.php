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
		if (($type !== "test") && !Model_Notification::isValidType($type))
		{
			throw new InvalidArgumentException("Unknown TYPE '{$type}' to create a notification with.");
		}
		if ($actor->getId() === null)
		{
			throw new InvalidArgumentException("This actor has no ID.");
		}

		$parameters = array(
			"type" => $type,
			"actor_id" => $actor->getId()
		);

		$allowedReferenceKeys = array("group_id", "project_id", "user_id", "year");

		foreach ($references as $reference => $id)
		{
			if (!in_array($reference, $allowedReferenceKeys))
			{
				throw new InvalidArgumentException("Invalid reference '{$reference}'.");
			}
			$parameters[$reference] = intval($id);
		}

		$parameters["targets"] = array();
		/**
		 * This code is identical to the code in `/notifications.php` and should stay that way.
		 */
		foreach ($targets as $target)
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
			if (count($splitTarget) !== 2)
			{
				throw new InvalidArgumentException("Invalid target '{$target}' passed to Notification queue. Aborting.");
			}

			switch ($splitTarget[0])
			{
				case "group":
				case "project":
				case "user":
					$parameters["targets"][] = $target;
					break;
				default:
					throw new InvalidArgumentException(
						"Invalid target '{$target}' passed to Notification queue. Aborting."
					);
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
}