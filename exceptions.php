<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

final class HttpRedirectException extends Exception
{
	protected $location;

	/**
	 * @param int $code
	 * @param string $location
	 */
	public function __construct($code, $location)
	{
		parent::__construct("Redirecting to $location", $code);
		$this->location = $location;
	}

	/**
	 * @return string
	 */
	public function getLocation()
	{
		return $this->location;
	}
}

class HTTPStatusException extends Exception
{
	/**
	 * The status message that is associated with the status code
	 */
	protected $statusmessage;

	/**
	 * @param int $code
	 * @param string $message
	 * @param Exception $previous
	 */
	public function __construct($code, $message, Exception $previous = null)
	{
		$this->statusmessage = getHttpStatusForCode($code);

		if (empty($this->statusmessage))
			trigger_error(
				"Bad status code used in HTTPStatusException (" . $code . ") for: " . $message .
				(!empty($previous) ? " with exception " . (string)$previous : ""),
				E_USER_WARNING
			);

		if (empty($message))
			$message = $this->statusmessage;

		parent::__construct($message, $code, $previous);
	}

	public function getStatusMessage()
	{
		return $this->statusmessage;
	}

	public function __toString()
	{
		return sprintf(
			"HTTP %d (%s): %s",
			$this->getCode(), $this->statusmessage,
			$this->getMessage()
		);
	}
}


final class PHPException extends Exception
{
	/**
	 * @param int $error_no
	 * @param string $error_string
	 * @param string $error_file
	 * @param string $error_line
	 * @param string $error_context
	 * @param Exception $previous
	 */
	public function __construct($error_no, $error_string, $error_file, $error_line, $error_context, Exception $previous = null)
	{
		parent::__construct('PHPException: '.$errstr, $errno, $previous);
		$this->file = $errfile;
		$this->line = $errline;
	}
}

final class RequestException extends Exception {}