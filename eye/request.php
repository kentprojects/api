<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Request
{
	public static $expires = 600;
	public static $key;
	public static $secret;

	/**
	 * @param array $params
	 * @return void
	 */
	public static function checksum(array &$params)
	{
		unset($params["sig"]);
		ksort($params);
		array_walk($params, function (&$v, $k)
		{
			$v = (string)$v;
		});
		$params["signature"] = md5(static::$secret . serialize($params));
	}

	public $method = "GET";
	public $params = array();
	public $url;
}

$request = new Request();
$signrequest = true;
$urlparams = array();

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
	/**
	 * Grab the correct keys from the application.ini file.
	 */
}
elseif (strpos($request->url, "?") > 1)
{
	parse_str(substr(strstr($request->url, "?"), 1), $urlparams);
	$request->url = strstr($request->url, "?", true);
}

switch ($request->method)
{

	case "GET":
		$urlparams = array_merge($urlparams, $request->params);
		break;

	case "POST":
	case "PUT":
		if (!empty($request->params))
		{
			$request->params = http_build_query($request->params, "", "&");
		}
		else
		{
			$request->params = "";
		}
		break;

}

if ($signrequest)
{
	$urlparams = array_merge($urlparams, array(
		"key" => Request::$key,
		"expires" => time() + Request::$expires
	));
	Request::checksum($urlparams);
}

if (!empty($urlparams))
{
	$request->url = sprintf("%s?%s", $request->url, http_build_query($urlparams, "", "&"));
}

// Initiate the CURL object.
$ch = curl_init();
$fh = null;

switch ($request->method)
{

	case "GET":
		// Do nothing.
		break;

	case "POST":
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request->params);
		break;

	case "PUT":
		$length = strlen($request->params);
		$fh = fopen("php://memory", "rw");
		fwrite($fh, $request->params);
		rewind($fh);
		curl_setopt($ch, CURLOPT_INFILE, $fh);
		curl_setopt($ch, CURLOPT_INFILESIZE, $length);
		curl_setopt($ch, CURLOPT_PUT, true);
		break;

	case "DELETE":
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		break;

}

curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
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

	<p><a href="{$request->url}" target="_blank">{$request->url}</a></p>
	<pre>{$response["body"]}</pre>
	<pre>{$response["info"]}</pre>

EOT;
?>