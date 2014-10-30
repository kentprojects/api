<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
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
			"username" => "convenor"
		),
		"1e9a755d73865da9068f079d81402ce7" => array(
			"role" => "staff",
			"username" => "supervisor1"
		),
		"6f2653c2a1c64220e3d2a713cc52b438" => array(
			"role" => "staff",
			"username" => "supervisor2"
		),
		"1f18ed87771daf095e090916cb9423e4" => array(
			"role" => "student",
			"username" => "student1"
		),
		"1460357d62390ab9b3b33fa1a0618a8f" => array(
			"role" => "student",
			"username" => "student2"
		),
		"930144ea545ce754789b15074106bc36" => array(
			"role" => "student",
			"username" => "student3"
		),
	);
	protected $success = "/success.html";

	/**
	 * The main action this authentication provider uses.
	 *
	 * @throws HTTPStatusException
	 * @throws HttpRedirectException
	 * @return void
	 */
	public function action()
	{
		if ($this->request->query("auth") === null)
		{
			throw new HTTPStatusException(400, "Missing state code.");
		}

		$url = parse_url($_SERVER["HTTP_REFERER"]);

		if (!array_key_exists($this->request->query("auth"), $this->fakecodes))
		{
			throw new HTTPStatusException(400, "Invalid state code.");
		}

		$authUser = $this->fakecodes[$this->request->query("auth")];

		// print_r($url); print_r($authUser); exit(1);

		throw new HttpRedirectException(302, $url["scheme"]."://".$url["host"].$this->success."#".md5(uniqid()));
	}
}