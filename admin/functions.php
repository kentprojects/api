<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
define("ADMIN_PATH", __DIR__);
require_once ADMIN_PATH . "/exceptions.php";

/**
 * Register the autoloader so we can call on classes when we feel like it!
 */
spl_autoload_register(
/**
 * @param string $class
 * @return bool
 */
	function ($class)
	{
		$file = str_replace("_", "/", strtolower($class)) . ".php";
		$filename = null;

		/**
		 * If the word "Admin_Controller_" exists at the beginning of this class, handle it.
		 */
		if (strpos($class, "Admin_Controller_") === 0)
		{
			$filename = ADMIN_PATH . "/controllers/" . str_replace("admin/controller/", "", $file);
		}
		/**
		 * Else if the word "Admin_Model_" exists at the beginning of this class, handle it.
		 */
		elseif (strpos($class, "Admin_Model_") === 0)
		{
			$filename = ADMIN_PATH . "/models/" . str_replace("admin/model/", "", $file);
		}
		/**
		 * Else if the word "Admin_" exists at the beginning of this class, handle it.
		 */
		elseif (strpos($class, "Admin_") === 0)
		{
			$filename = ADMIN_PATH . "/" . str_replace("admin/", "", $file);
		}
		/**
		 * Else this is a generic class in a folder, so go find it!
		 */
		else
		{
			$folders = array(
				ADMIN_PATH . "/classes",
				ADMIN_PATH . "/views/elements",
				ADMIN_PATH . "/views/pages",
				ADMIN_PATH . "/views"
			);

			foreach ($folders as $folder)
			{
				if (file_exists($folder . "/" . $file))
				{
					$filename = $folder . "/" . $file;
					break;
				}
			}
		}

		if (empty($filename) || !file_exists($filename))
		{
			return false;
		}

		/** @noinspection PhpIncludeInspection */
		require_once $filename;

		return class_exists($class, false);
	}
);