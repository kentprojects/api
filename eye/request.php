<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class EyeRequest
{
	public static $expires = 600;
	public static $key;
	public static $salt = "";
	public static $secret;

	/**
	 * @param array $params
	 * @return void
	 */
	public static function checksum(array &$params)
	{
		unset($params["signature"]);
		ksort($params);
		array_walk($params, function (&$v)
		{
			$v = (string)$v;
		});
		$params["signature"] = md5(static::$salt . static::$secret . json_encode($params));
	}

	public $body = "";
	public $headers = array(
		"Accept" => "application/json",
		"Content-Type" => "application/json"
	);
	public $method = "GET";
	public $params = array();
	public $url;

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

$config = parse_ini_file(__DIR__ . "/config.ini", true);
$request = new EyeRequest;
$signRequest = true;
$urlParams = array();

if (!empty($_POST["method"]))
{
	$request->method = strtoupper($_POST["method"]);
}

if (!empty($_POST["url"]))
{
	$request->url = $_POST["url"];
}
EyeRequest::$salt = (stripos($request->url, "api.dev") === false) ? $config["live-api"] : $config["dev-api"];

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
	/**
	 * Grab the correct keys from the application.ini file.
	 */
	$applications = parse_ini_file(__DIR__ . "/../applications.ini", true);
	if (empty($applications[$_POST["key"]]))
	{
		echo '<div class="alert alert-danger">',
		'<strong>There was an error with your API key</strong><br/>',
		'The API key was not found in the list of active applications.',
		'</div>';
	}
	else
	{
		$request::$key = $applications[$_POST["key"]]["key"];
		$request::$secret = $applications[$_POST["key"]]["secret"];
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
if (!empty($request->body))
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
		"key" => EyeRequest::$key,
		"expires" => time() + EyeRequest::$expires
	));
	EyeRequest::checksum($urlParams);
}

if (!empty($urlParams))
{
	$request->url = sprintf("%s?%s", $request->url, http_build_query($urlParams, "", "&"));
}

// Initiate the CURL object.
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

curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_URL, $request->url);

// Run the CURL request.
$response = array(
	"body" => curl_exec($ch),
	"info" => curl_getinfo($ch),
	"json" => null
);

if ($request->method == "PUT")
{
	fclose($fh);
}

$response["info"] = print_r($response["info"], true);
$response["json"] = json_decode($response["body"]);
if (!empty($response["json"]))
{
	$response["body"] = json_encode($response["json"], JSON_PRETTY_PRINT);
}

echo <<<EOT

	<hr/>

	<p><pre><a href="{$request->url}" target="_blank">{$request->url}</a></pre></p>
	<pre>{$response["body"]}</pre>
	<pre>{$response["info"]}</pre>

EOT;
