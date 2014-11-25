<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

abstract class Request
{
	/**
	 * Allowed constants to Request.
	 * These represent the allowed request methods.
	 * @var string
	 */
	const GET = "request:get";
	const POST = "request:post";
	const PUT = "request:put";
	const DELETE = "request:delete";

	/**
	 * A list of allowed methods built using the constants above.
	 * @var array
	 */
	protected static $allowedMethods;

	/**
	 * Gets the constants from this class (the methods) and returns them as a numeric index array.
	 * @return array
	 */
	protected static function getMethods()
	{
		if (empty(static::$allowedMethods))
		{
			$rf = new ReflectionClass(get_called_class());
			static::$allowedMethods = $rf->getConstants();
		}
		return static::$allowedMethods;
	}

	/**
	 * @param string $method A string relevant constant representing the type of request.
	 * @param string $url A URL to run the request to.
	 * @throws Exception
	 * @return Request_Internal | Request_External
	 */
	public static function factory($method, $url)
	{
		$parsed = parse_url($url);

		if (!empty($parsed["host"]))
		{
			return new Request_External($method, $url);
		}

		if (!empty($parsed["path"]))
		{
			return new Request_Internal($method, $url);
		}

		throw new Exception("Bad URL for Request: '$url'");
	}

	/**
	 * Translate a string into a relevant Request constant.
	 *
	 * @param string
	 * @throws Exception
	 * @return string
	 */
	public static function stringToMethod($method)
	{
		$method  = strtoupper($method);
		$methods = static::getMethods();

		if (empty($methods[$method]))
			throw new Exception("Unable to determine Request constant for '{$method}'.");

		return $methods[$method];
	}

	/**
	 * Request content type
	 * @var string
	 */
	private $content_type;
	/**
	 * Request headers
	 * @var array
	 */
	private $headers = array();
	/**
	 * Request method
	 * @var string
	 */
	private $method;
	/**
	 * Request post data (used if POST or PUT)
	 * @var array
	 */
	private $post = array();
	/**
	 * Request query string
	 * @var array
	 */
	private $query = array();
	/**
	 * Request URL
	 * @var string
	 */
	protected $url;

	/**
	 * Builds a new Request object.
	 *
	 * @param string $method
	 * @param string $url
	 */
	protected function __construct($method, $url)
	{
		$this->method = $method;
		$this->url = $url;
	}

	/**
	 * Run the request!
	 * @return Response
	 */
	public abstract function execute();

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->content_type;
	}

	/**
	 * Return a specific header.
	 *
	 * @param string $key
	 * @return string
	 */
	public function getHeader($key)
	{
		return !empty($this->headers[$key]) ? $this->headers[$key] : null;
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return array
	 */
	public function getPostData()
	{
		return $this->post;
	}

	/**
	 * @return array
	 */
	public function getQueryData()
	{
		return $this->query;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Returns POST data
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function post($key, $default = null)
	{
		return empty($this->post[(string)$key])
			? $default
			: $this->post[(string)$key];
	}

	/**
	 * Returns GET data
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function query($key, $default = null)
	{
		return empty($this->query[(string)$key])
			? $default
			: $this->query[(string)$key];
	}

	/**
	 * Set the content type.
	 * Also set the relevant header.
	 *
	 * @param string
	 * @return $this
	 */
	public function setContentType($type)
	{
		$this->content_type = $type;
		$this->headers["Content-Type"] = $type;
		return $this;
	}

	/**
	 * @param array
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, $headers);
		return $this;
	}

	/**
	 * @param string|array
	 * @return $this
	 */
	public function setPostData($postData)
	{
		$this->post = !empty($postData) ? $postData : array();
		return $this;
	}

	/**
	 * @param array
	 * @return $this
	 */
	public function setQueryData(array $queryData)
	{
		$this->query = array_merge($this->query, $queryData);
		return $this;
	}
}

class Request_External extends Request
{
	/**
	 * Run the external request!
	 * @return Response
	 */
	public function execute()
	{
		/**
		 * Build the URL
		 * With a query string, if required.
		 */
		$url = $this->getUrl();
		$query = $this->getQueryData();
		if (!empty($query))
		{
			$url .= "?" . http_build_query($query, null, "&");
		}

		/**
		 * Initialise the curl handle and set some values.
		 */
		$ch = curl_init($url);
		$fh = null;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getCurlHeaders());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		/**
		 * CURL requirements if we're doing fancy methods.
		 */
		switch($this->getMethod())
		{
			case Request::POST:
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getPostData());
				break;
			case Request::PUT:
				$body = $this->getPostData();
				$length = strlen($body);
				$fh = fopen('php://memory', 'rw');
				fwrite($fh, $body);
				rewind($fh);
				curl_setopt($ch, CURLOPT_INFILE, $fh);
				curl_setopt($ch, CURLOPT_INFILESIZE, $length);
				curl_setopt($ch, CURLOPT_PUT, true);
				break;
		}

		/**
		 * Build a new Response object based on the return data.
		 */
		$response = new Response;
		$response->body(curl_exec($ch));
		$response->headers(curl_getinfo($ch));

		/**
		 * Cleanup the PUT requests.
		 */
		if ($this->getMethod() === Request::PUT)
		{
			fclose($fh);
		}

		/**
		 * Close the CURL handle.
		 */
		curl_close($ch);

		/**
		 * Manipulate the results to fill our response object.
		 */
		$response->status($response->header("http_code"));

		return $response;
	}

	private function getCurlHeaders()
	{
		$headers = array();
		foreach($this->getHeaders() as $key => $value)
		{
			$headers[] = "$key: $value";
		}
		return $headers;
	}
}

class Request_Internal extends Request
{
	/**
	 * Request parameter data from the router.
	 * @var array
	 */
	private $param = array();
	private $run = false;

	/**
	 * Run the internal request!
	 * @throws RequestException
	 * @return Response
	 */
	public function execute()
	{
		if ($this->run === true)
		{
			/**
			 * This prevents idiots running something akin to: $this->request->execute();
			 */
			throw new RequestException(sprintf(
				"You cannot execute this internal request within it's own execute process: %s:%s ",
				strtoupper($_SERVER["REQUEST_METHOD"]), $_SERVER["PATH_INFO"]
			));
		}
		$this->run = true;

		try
		{
			$this->param = Router::handle($this->getUrl());

			// Load the relevant Controller
			$controller = "Controller_" . ucfirst($this->param("controller"));
			if (!class_exists($controller))
			{
				throw new RequestException("$controller was not found.");
			}
			if (!is_subclass_of($controller, "Controller"))
			{
				throw new RequestException("$controller does not extend Controller.");
			}

			// Check the action is valid.
			$action = "action_" . $this->param("action");
			if (!method_exists($controller, $action))
			{
				throw new RequestException("Method $controller::$action was not found.");
			}

			// print_r($this); var_dump($controller, $action); exit();

			$response = new Response($this);

			// Run the Controller and the relevant Action
			/** @var Controller $controller */
			$controller = new $controller($this, $response);
			$controller->before();
			$controller->$action();
			$controller->after();

			return $response;
		}
		catch (HTTPRedirectException $e)
		{
			/**
			 * Handle the redirect, by creating a new Response object.
			 */
			$response = new Response($this);
			$response->status($e->getCode());
			$response->header("Location", $e->getLocation());
			return $response;
		}
		catch(Exception $e)
		{
			$response = new Response($this);
			$response->headers(array(
				"Exception" => get_class($e)
			));

			if (class_exists("Controller_Error"))
			{
				/**
				 * Some clever way to handle errors, because the frontend is gonna handle exceptions prettier than the API will.
				 *
				 * @var Controller_Error $controller
				 */
				$controller = new Controller_Error($this, $response);
				$controller->before();
				$controller->action($e);
				$controller->after();
			}
			else
			{
				/**
				 * If we don't have an error controller, just kill it.
				 */
				$response->body((string)$e);
			}

			return $response;
		}
	}

	/**
	 * Returns parameters from the route.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function param($key, $default = null)
	{
		return empty($this->param[(string)$key])
			? $default
			: $this->param[(string)$key];
	}

	public function setParamData(array $params)
	{
		$this->param = $params;
	}
}