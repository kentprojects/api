<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Admin_Router
 * This represents routing a URL to a correct controller with a valid actions and parameters.
 */
final class Admin_Router
{
	/**
	 * "Parse" the path given (to a degree) and return array of valid parameters.
	 *
	 * E.g. /user/22
	 *  =>    array(
	 *            "controller" => "user",
	 *            "action" => "index",
	 *            "id" => 22
	 *        )
	 * E.g. /project/12/comment/45
	 *  =>    array(
	 *            "controller" => "project",
	 *            "action" => "comment",
	 *            "id" => 12,
	 *            "id2" => "45"
	 *        )
	 * E.g. /comment/thread
	 *  =>    array(
	 *            "controller" => "comment",
	 *            "action" => "thread"
	 *        )
	 *
	 * @param string $url
	 * @return array
	 */
	public static function handle($url)
	{
		$params = array();

		$params["controller"] = "home";
		$params["action"] = "index";

		$url = explode("/", strtolower($url));
		array_shift($url);

		$continue = true;
		$target = "controller";

		/**
		 * Loop through the URL segments, placing them into their rightful place.
		 */
		while ($continue && count($url) > 0)
		{
			$segment = array_shift($url);

			if (empty($segment))
			{
				return $params;
			}

			switch ($target)
			{
				/**
				 * If we're searching for a controller, then assign it in the parameter array and change the target.
				 */
				case "controller":
					$params["controller"] = $segment;
					$target = "id";
					break;
				/**
				 * If we're searching for an ID, then assign it in the parameter array and change the target.
				 */
				case "id":
					if (is_numeric($segment))
					{
						$params["id"] = intval($segment);
					}
					else
					{
						/**
						 * If this segment isn't an integer, then we're not looking at an ID. This must be an action:
						 * E.g. /controller/action instead of /controller/:id/action
						 * So make it become the action we'll be searching for next.
						 */
						array_unshift($url, $segment);
					}
					$target = "action";
					break;
				/**
				 * If we're searching for an action, then assign it in the parameter array and change the target.
				 */
				case "action":
					$params["action"] = $segment;
					$target = "id2";
					break;
				/**
				 * If we STILL have a segment, let's call it a second ID and move on with it.
				 * E.g. /controller/:id/action/:id2 OR /controller/:action/:id2
				 */
				case "id2":
					$params["id2"] = $segment;
					$continue = false;
					break;
			}
		}

		return $params;
	}
}