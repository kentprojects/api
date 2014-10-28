<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * This script is the main entry point for authentication with the API.
 * In the production environment, this script will be exposed as a separate domain in order to minimise direct API exposure.
 * However, it requires direct access to the API and the Database, so the source files for it are included here.
 */
define("PROJECT", "kentprojects-authentication");
require_once __DIR__."/functions.php";

/** @noinspection PhpParamsInspection */
$request = Request::factory(
	Request::stringToMethod($_SERVER["REQUEST_METHOD"]),
	empty($_SERVER["REQUEST_URI"]) ? "/" : $_SERVER["REQUEST_URI"]
);

if (!($request instanceof Request_Internal))
{
	exit(
		(string) new RequestException(sprintf(
			"Request %s:%s did not return an internal request.",
			strtoupper($_SERVER["REQUEST_METHOD"]),
			empty($_SERVER["PATH_INFO"]) ? "/" : $_SERVER["PATH_INFO"]
		))
	);
}

$request->setHeaders(apache_request_headers());
$request->setQueryData($_GET);
$request->setPostData($_POST);

$response = new Response;

try
{
	$url = explode("/", str_replace("/auth", "", $request->getUrl()));

	$provider = array_shift($url);
	if (empty($provider))
	{
		throw new InvalidArgumentException("No provider passed to authentication.");
	}
	$provider = "Authentication_".ucfirst($provider);
	if (!class_exists($provider))
	{
		throw new InvalidArgumentException("Provider ".ucfirst($provider)." doesn't exist.");
	}

	$action = array_shift($url);
	if (empty($action))
	{
		$action = "action";
	}
	if (!method_exists($provider, $action))
	{
		throw new InvalidArgumentException("Action ".$action." doesn't exist for ".$provider);
	}

	$provider = new $provider($request, $response);
	$provider->$action();
}
catch (HttpRedirectException $e)
{
	$response = new Response;
	$response->header("Location", $e->getLocation());
	$response->status($e->getCode());
	$response->send();
}
catch (Exception $e)
{
	$response = new Response;
	$response->body((string)$e);
	$response->headers(array(
		"Exception" => get_class($e)
	));
	$response->status(500);
	$response->send();
}