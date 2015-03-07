<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

/**
 * Class CacheException
 */
final class CacheException extends Exception
{
}

/**
 * Class DatabaseException
 */
final class DatabaseException extends Exception
{
	protected $query;
	protected $types;
	protected $params;

	/**
	 * @param string $error_message
	 * @param int $error_no
	 * @param string $query
	 * @param string $types
	 * @param array $params
	 */
	public function __construct($error_message, $error_no = 0, $query = null, $types = null, $params = null)
	{
		parent::__construct($error_message, $error_no);
		$this->query = $query;
		$this->types = $types;
		$this->params = $params;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function getTypes()
	{
		return $this->types;
	}
}

/**
 * Class FormException
 */
class FormException extends Exception
{
}

/**
 * Class HttpRedirectException
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

/**
 * Class HttpStatusException
 */
class HttpStatusException extends Exception
{
	/**
	 * @var string
	 */
	protected $data = array();
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * The status message that is associated with the status code.
	 *
	 * @var string
	 */
	protected $status_message;

	/**
	 * @param int $code
	 * @param string $message
	 * @param Exception $previous
	 */
	public function __construct($code, $message = null, Exception $previous = null)
	{
		$this->name = __CLASS__;
		$this->status_message = getHttpStatusForCode($code);

		if (empty($this->status_message))
		{
			trigger_error(
				"Bad status code used in HttpStatusException (" . $code . ") for: " . $message .
				(!empty($previous) ? " with exception " . (string)$previous : ""),
				E_USER_WARNING
			);
		}

		if (empty($message))
		{
			$message = $this->status_message;
		}

		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"HTTP %d (%s): %s",
			$this->getCode(), $this->status_message,
			$this->getMessage()
		);
	}

	/**
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getStatusMessage()
	{
		return $this->status_message;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
}

/**
 * Class IntentException
 */
final class IntentException extends Exception
{
}

/**
 * Class PHPException
 */
final class PHPException extends Exception
{
	protected $context;
	protected $file;
	protected $line;

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
		parent::__construct($error_string, $error_no, $previous);

		$this->context = $error_context;
		$this->file = $error_file;
		$this->line = $error_line;
	}
}

/**
 * Class RequestException
 */
final class RequestException extends Exception
{
}

/**
 * Class ValidationException
 */
final class ValidationException extends FormException
{
}