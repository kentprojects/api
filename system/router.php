<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class Router
 * This represents routing a URL to a correct controller with a valid actions and parameters.
 */
final class Router
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

		$params["controller"] = "welcome";
		$params["action"] = "index";

		$url = explode("/", strtolower($url));
		array_shift($url);

		$continue = true;
		$target = "controller";

		while($continue && count($url) > 0)
		{
			$segment = array_shift($url);

			if (empty($segment))
			{
				return $params;
			}

			switch($target)
			{
				case "controller":
					$params["controller"] = $segment;
					$target = "id";
					break;
				case "id":
					if (is_numeric($segment))
					{
						$params["id"] = intval($segment);
					}
					else
					{
						array_unshift($url, $segment);
					}
					$target = "action";
					break;
				case "action":
					$params["action"] = $segment;
					$target = "id2";
					break;
				case "id2":
					$params["id2"] = $segment;
					$continue = false;
					break;
			}
		}

		return $params;
	}
}