<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
require_once __DIR__ . "/functions.php";

Timing::start("api");

/** @noinspection PhpParamsInspection */
$request = Request::factory(Request::stringToMethod($_SERVER["REQUEST_METHOD"]), $_SERVER["PATH_INFO"]);

if (!($request instanceof Request_Internal))
{
	exit((string)new RequestException(sprintf(
		"Request %s:%s did not return an internal request.",
		strtoupper($_SERVER["REQUEST_METHOD"]),
		empty($_SERVER["PATH_INFO"]) ? "/" : $_SERVER["PATH_INFO"]
	)));
}

/**
 * Set the request header information.
 */
$request->setHeaders(apache_request_headers());

/**
 * Set the GET and POST data.
 */
$request->setQueryData($_GET);
$request->setPostData(json_decode(file_get_contents("php://input"), true));

/**
 * Execute the request and send the response.
 */
$response = $request->execute();

Timing::stop("api");

if (isset($_GET["timing"]))
{
	$response->header("X-Timing-Body", "true");
	$response->body(Timing::export());
}
else
{
	$timings = Timing::export(true);
	$response->header("X-Timing", $timings->length);
}

$response->send();