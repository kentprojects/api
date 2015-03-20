<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Admin_Router
{
	/**
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

		while ($continue && count($url) > 0)
		{
			$segment = array_shift($url);

			if (empty($segment))
			{
				return $params;
			}

			switch ($target)
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