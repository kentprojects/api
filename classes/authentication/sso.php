<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

/**
 * @require The external SimpleSAML2 library.
 */
/** @noinspection PhpIncludeInspection */
/** @noinspection SpellCheckingInspection */
require_once "/var/www/simplesaml/lib/_autoload.php";

final class Authentication_SSO extends Authentication_Abstract
{
	protected $authentication = Auth::NONE;
	protected $backupUrl = "http://kentprojects.com";
	protected $emailDomain = "@kent.ac.uk";
	protected $success = "/login.php";

	/**
	 * The main action this authentication provider uses.
	 *
	 * @throws HttpStatusException
	 * @throws HttpRedirectException
	 * @return void
	 */
	public function action()
	{
		session_name("KentProjectsAuthentication");
		session_start();

		if (!empty($_SERVER["HTTP_REFERER"]) && empty($_SESSION["incoming-url"]))
		{
			$_SESSION["incoming-url"] = $_SERVER["HTTP_REFERER"];
		}

		/** @noinspection PhpUndefinedClassInspection */
		$provider = new SimpleSAML_Auth_Simple("default-sp");
		/** @noinspection PhpUndefinedMethodInspection */
		$provider->requireAuth();
		/** @var array $attributes */
		/** @noinspection PhpUndefinedMethodInspection */
		$attributes = $provider->getAttributes();

		if (true)
		{
			print_r($attributes);
			exit(1);
		}

		$user = Model_User::getByEmail($attributes["username"] . $this->$emailDomain);
		if (empty($user))
		{
			$user = new Model_User;
			$user->setEmail($attributes["username"] . $this->$emailDomain);
			$user->setRole($attributes["role"]);
			$user->save();
		}

		$url = parse_url(!empty($_SESSION["incoming-url"]) ? $_SESSION["incoming-url"] : $this->$backupUrl);

		session_destroy();

		throw new HttpRedirectException(
			302, $url["scheme"] . "://" . $url["host"] . (!empty($url["port"]) ? ":" . $url["port"] : "") .
			$this->success . "?success=" . $this->generateAuthToken($user)
		);
	}
}