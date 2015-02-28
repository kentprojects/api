<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class EyeRequest
{
	/**
	 * @var Model_Application
	 */
	public static $application;
	/**
	 * @var int
	 */
	public static $expires = 600;
	/**
	 * @var string
	 */
	public static $salt;
	/**
	 * @var Model_Token
	 */
	public static $userToken;

	/**
	 * @param array $params
	 * @return void
	 */
	public static function checksum(array &$params)
	{
		unset($params["signature"]);
		ksort($params);
		array_walk(
			$params,
			function (&$v)
			{
				$v = (string)$v;
			}
		);

		$local = md5(config("checksum", "salt") . static::$application->getSecret() . json_encode($params));

		if (false)
		{
			error_log(json_encode(array(
				"INVALIDATED" => "SIGNATURE",
				"local" => $local,
				"get" => $params,
				"app" => static::$application,
				"sum" => config("checksum", "salt") . static::$application->getSecret() . json_encode($params)
			)));
		}

		$params["signature"] = $local;
	}

	/**
	 * @var string
	 */
	public $body = "";
	/**
	 * @var array
	 */
	public $headers = array(
		"Accept" => "application/json",
		"Content-Type" => "application/json"
	);
	/**
	 * @var string
	 */
	public $method = "GET";
	/**
	 * @var array
	 */
	public $params = array();
	/**
	 * @var string
	 */
	public $url;

	/**
	 * @return string[]
	 */
	public function getHeaders()
	{
		$headers = array();
		foreach ($this->headers as $header => $value)
		{
			$headers[] = "{$header}: {$value}";
		}
		return $headers;
	}
}

require_once __DIR__ . "/../functions.php";

/**
 * @var Model_Application[] $applications
 */
$applications = array(
	Model_Application::getById(1),
	Model_Application::getById(2)
);

/**
 * @var Model_User[] $users
 */
$users = array(
	Model_User::getByEmail("J.C.Hernandez-Castro@kent.ac.uk"),
	Model_User::getByEmail("jsd24@kent.ac.uk"),
	Model_User::getByEmail("mh471@kent.ac.uk"),
	Model_User::getByEmail("supervisor2@kent.ac.uk")
);

/**
 * @var EyeRequest
 */
$request = new EyeRequest;
/**
 * @var bool
 */
$signRequest = true;
/**
 * @var array
 */
$urlParams = array();

if (!empty($_POST["method"]))
{
	$request->method = strtoupper($_POST["method"]);
}

if (!empty($_POST["url"]))
{
	$request->url = $_POST["url"];
}

if (!empty($_POST["params-keys"]))
{
	for ($i = 0; $i < count($_POST["params-keys"]); $i++)
	{
		if ((!empty($_POST["params-keys"][$i])) && (!empty($_POST["params-values"][$i])))
		{
			$request->params[$_POST["params-keys"][$i]] = $_POST["params-values"][$i];
		}
	}
}

if (!empty($_POST["key"]))
{
	$application = null;
	foreach ($applications as $app)
	{
		if ($app->getKey() === $_POST["key"])
		{
			$application = $app;
		}
	}

	if (empty($application))
	{
		echo '<div class="alert alert-danger">',
		'<strong>There was an error with your API key</strong><br/>',
		'The API key was not found in the list of active applications.',
		'</div>';
	}
	else
	{
		$request::$application = $application;
	}
}

if (!empty($_POST["user"]))
{
	$user = null;
	foreach ($users as $u)
	{
		if ($u->getId() == $_POST["user"])
		{
			$user = $u;
		}
	}

	if (empty($user))
	{
		echo '<div class="alert alert-danger">',
		'<strong>There was an error with your User ID</strong><br/>',
		'The User ID was not found in the list of users.',
		'</div>';
	}
	else
	{
		$request::$userToken = Model_Token::generate($application, $user);
	}
}

if (!empty($_POST["params-body"]))
{
	$request->body = json_decode($_POST["params-body"]);
	if (json_last_error_msg() !== "No error")
	{
		echo '<div class="alert alert-danger">',
		'<strong>There was an error with your JSON input</strong><br/>',
		json_last_error_msg(),
		'</div>';
	}
}

if (strpos($request->url, "?") > 1)
{
	parse_str(substr(strstr($request->url, "?"), 1), $urlParams);
	$request->url = strstr($request->url, "?", true);
}

$urlParams = array_merge($urlParams, $request->params);

if (!empty($request->body) && ($request->method != "GET"))
{
	$request->body = json_encode($request->body);
	$request->headers["Content-Length"] = strlen($request->body);
}
else
{
	$request->body = "";
}

if ($signRequest)
{
	$urlParams = array_merge($urlParams, array(
		"key" => $request::$application->getKey(),
		"expires" => time() + $request::$expires
	));
	if (!empty($request::$userToken))
	{
		$urlParams = array_merge($urlParams, array(
			"user" => $request::$userToken->getToken()
		));
	}
	EyeRequest::checksum($urlParams);
}

if (!empty($urlParams))
{
	$request->url = sprintf("%s?%s", $request->url, http_build_query($urlParams, "", "&"));
}

/**
 * Initiate the CURL object.
 */
$ch = curl_init();
$fh = null;

switch ($request->method)
{
	case "POST":
	case "DELETE":
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->method);
		if (!empty($request->body))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
		}
		break;

	case "PUT":
		curl_setopt($ch, CURLOPT_PUT, true);
		if (!empty($request->body))
		{
			$fh = fopen("php://memory", "rw");
			fwrite($fh, $request->body);
			rewind($fh);
			curl_setopt($ch, CURLOPT_INFILE, $fh);
			curl_setopt($ch, CURLOPT_INFILESIZE, $request->headers["Content-Length"]);
		}
		break;
}

curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_URL, $request->url);

$response = curl_exec($ch);
if (true)
{
	error_log($response);
	echo <<<EOT
	<hr/>
	<p><pre><a href="{$request->url}" target="_blank">{$request->url}</a></pre></p>
	<pre><code>{$response}</code></pre>
EOT;
	exit();
}

try
{
	$splitResponse = explode("\r\n\r\n", $response, 2);
	$response = array_pop($splitResponse);
	$headers = explode("\n", array_pop($splitResponse));
	$httpHeader = array_shift($headers);
	sort($headers);
	array_unshift($headers, $httpHeader);
	error_log(print_r($headers, true));
	$headers = implode("\n", $headers);
	unset($splitResponse, $httpHeader);
}
catch (Exception $e)
{
	$headers = "Failed to build a response from the CURL request.";
	$response = json_encode("Maybe the CURL request received no data?");
}

/**
 * Run the CURL request.
 *
 * @var array
 */
$output = array(
	"body" => $response,
	"headers" => $headers,
	"info" => curl_getinfo($ch),
	"json" => null
);

if (!empty($fh))
{
	fclose($fh);
}

$output["info"] = print_r($output["info"], true);
$output["json"] = json_decode($output["body"]);
if (!empty($output["json"]))
{
	/**
	 * @var array|stdClass|null
	 */
	$output["body"] = json_encode($output["json"], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
else
{
	$output["body"] = $output["json"];
}

echo <<<EOT
	<hr/>

	<p><pre><a href="{$request->url}" target="_blank">{$request->url}</a></pre></p>
	<pre><code>{$output["headers"]}</code></pre>
	<pre><code>{$output["body"]}</code></pre>
	<pre>{$output["info"]}</pre>
EOT;
