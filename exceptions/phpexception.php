<?php if (!defined("PROJECT")) exit("Direct script access is forbidden.");
/**
 * @category: Exceptions
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
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