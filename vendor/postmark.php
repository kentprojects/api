<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Postmark extends Mail
{
	/**
	 * @var array
	 */
	protected $data;

	public function __construct()
	{
		/** @noinspection SpellCheckingInspection */
		if (config("postmark", "key") === "postmark-api-key")
		{
			/** @noinspection SpellCheckingInspection */
			throw new InvalidArgumentException("Default Postmark API key in config.");
		}

		/** @noinspection SpellCheckingInspection */
		$this->data = array(
			"From" => config("mail", "from-email")
		);
	}

	/**
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function send()
	{
		/** @var Request_External $request */
		/** @noinspection SpellCheckingInspection */
		$request = Request::factory(Request::POST, "https://api.postmarkapp.com/email");
		$request->setHeaders(array(
			"Accept" => "application/json",
			"Content-Type" => "application/json",
			"X-Postmark-Server-Token" => config("postmark", "key")
		));

		if (empty($this->data["To"]))
		{
			throw new InvalidArgumentException("No recipients has been set.");
		}
		elseif (empty($this->data["Subject"]))
		{
			throw new InvalidArgumentException("No subject has been set.");
		}
		elseif (empty($this->data["TextBody"]))
		{
			throw new InvalidArgumentException("No body has been set.");
		}

		$this->data["To"] = implode(", ", $this->data["To"]);

		Log::debug($this->data);

		$request->setPostData(json_encode($this->data));
		/** @var Response $response */
		$response = $request->execute();
		$response->body(json_decode($response->body()));

		Log::debug($response);

		return ($response->status() < 300);
	}

	/**
	 * @param string|array $body
	 * @return void
	 */
	public function setBody($body)
	{
		if (is_array($body))
		{
			$body = implode(" ", array_map(
				function ($string)
				{
					return trim($string, " \r\0\x0B");
				},
				$body
			));
		}
		$this->data["TextBody"] = $body;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->data["Subject"] = $subject;
	}

	/**
	 * @param string $email
	 * @param string $name
	 */
	public function setTo($email, $name)
	{
		if (empty($this->data["To"]))
		{
			$this->data["To"] = array();
		}
		$this->data["To"][] = "$name <$email>";

	}
}