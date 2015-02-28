<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Error extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::NONE;

	/**
	 * When an error occurs.
	 *
	 * @param Exception $e
	 * @return void
	 */
	public function action(Exception $e)
	{
		$error = array(
			"error" => true,
			"exception" => get_class($e),
			"message" => $e->getMessage()
		);
		$status = 500;

		switch (get_class($e))
		{
			case "DatabaseException":
				/** @var DatabaseException $e */
				if (config("environment") === "development")
				{
					$error["query"] = $e->getQuery();
					$error["types"] = $e->getTypes();
					$error["params"] = $e->getParams();
				}
				break;
			case "HttpStatusException":
				/** @var HttpStatusException $e */
				$error["status"] = $status = $e->getCode();
				break;
		}
		/** @var Exception $e */

		if (true)
		{
			$error["trace"] = explode("\n", $e->getTraceAsString());
		}

		$this->response->body($error);
		$this->response->status($status);
	}
}