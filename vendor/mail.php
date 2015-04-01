<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

require_once __DIR__ . "/phpmailer/class.phpmailer.php";
require_once __DIR__ . "/phpmailer/class.pop3.php";
require_once __DIR__ . "/phpmailer/class.smtp.php";

/**
 * Class Mail
 */
class Mail
{
	/**
	 * @var PhpMailer\PhpMailer
	 */
	protected $mailer;

	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct()
	{
		/** @noinspection SpellCheckingInspection */
		if ((config("mail", "username") === "someuser@gmail.com") || (config("mail", "password") === "password"))
		{
			throw new InvalidArgumentException("Default username / password in config > mail");
		}

		$this->mailer = new PhpMailer\PHPMailer;

		$this->mailer->isSMTP();
		/**
		 * Enable SMTP debugging
		 *   0 = off (for production use)
		 *   1 = client messages
		 *   2 = client and server messages
		 */
		$this->mailer->SMTPDebug = 2;

		$this->mailer->Host = config("mail", "hostname");
		$this->mailer->Port = config("mail", "port");
		$this->mailer->SMTPAuth = true;
		$this->mailer->Username = config("mail", "username");
		$this->mailer->Password = config("mail", "password");

		/** @noinspection SpellCheckingInspection */
		if ($this->mailer->Host === "smtp.gmail.com")
		{
			$this->mailer->Port = 587;
			$this->mailer->SMTPSecure = 'tls';
		}

		$this->mailer->setFrom(config("mail", "from-email"), config("mail", "from-name"));
		$this->mailer->addReplyTo(config("mail", "reply-to-email"), config("mail", "reply-to-name"));
	}

	/**
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function send()
	{
		if (empty($this->mailer->Subject))
		{
			throw new InvalidArgumentException("No subject has been set.");
		}
		if (empty($this->mailer->Body))
		{
			throw new InvalidArgumentException("No body has been set.");
		}

		$send = $this->mailer->send();
		if (!$send)
		{
			error_log("That failed to send. Totally.");
		}

		return $send;
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
		$this->mailer->Body = $body;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->mailer->Subject = $subject;
	}

	/**
	 * @param $email
	 * @param $name
	 */
	public function setTo($email, $name)
	{
		$this->mailer->addAddress($email, $name);
	}
}