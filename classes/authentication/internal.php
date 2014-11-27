<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Authentication_Internal extends Authentication_Abstract
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;
	
	protected $fakecodes = array(
		"f4dfeada0e91e1791a80da1bb26a7d96" => array(
			"role" => "staff",
			"username" => "J.C.Hernandez-Castro"
		),
		"1e9a755d73865da9068f079d81402ce7" => array(
			"role" => "staff",
			"username" => "J.S.Crawford"
		),
		"6f2653c2a1c64220e3d2a713cc52b438" => array(
			"role" => "staff",
			"username" => "supervisor2"
		),
		"1f18ed87771daf095e090916cb9423e4" => array(
			"role" => "student",
			"username" => "mh471"
		),
		"1460357d62390ab9b3b33fa1a0618a8f" => array(
			"role" => "student",
			"username" => "jsd24"
		),
		"930144ea545ce754789b15074106bc36" => array(
			"role" => "student",
			"username" => "mjw59"
		),
	);
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
		if ($this->request->query("auth") === null)
		{
			throw new HttpStatusException(400, "Missing state code.");
		}
		
		$url = parse_url($_SERVER["HTTP_REFERER"]);
		
		if (!array_key_exists($this->request->query("auth"), $this->fakecodes))
		{
			throw new HttpStatusException(400, "Invalid state code.");
		}
		
		$authUser = $this->fakecodes[$this->request->query("auth")];
		
		// print_r($url); print_r($authUser); exit(1);
		
		$user = Model_User::getByEmail($authUser["username"]."@kent.ac.uk");
		if (empty($user))
		{
			$user = new Model_User;
			$user->setEmail($authUser["username"]."@kent.ac.uk");
			$user->setRole($authUser["role"]);
			$user->save();
		}

		throw new HttpRedirectException(302, $url["scheme"] . "://" . $url["host"] . (!empty($url["port"]) ? ":" . $url["port"] : "") . $this->success . "?auth=" . $this->generateAuthToken($user));
	}
}