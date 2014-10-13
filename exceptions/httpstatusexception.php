<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @category: Exceptions
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
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
				"Bad status code used in HTTPStatusException (".$code.") for: ".$message.
				(!empty($previous) ? " with exception ".(string)$previous : ""),
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