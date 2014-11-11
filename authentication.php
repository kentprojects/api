<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * This script is the main entry point for authentication with the API.
 * In the production environment, this script will be exposed as a separate domain in order to minimise direct API exposure.
 * However, it requires direct access to the API and the Database, so the source files for it based included here.
 */
require_once __DIR__."/functions.php";

/**
 * Build the main Request & the Response to be used by the API.
 *
 * @noinspection PhpParamsInspection */
$request = Request::factory(Request::stringToMethod($_SERVER["REQUEST_METHOD"]), $_SERVER["PATH_INFO"]);
if (!($request instanceof Request_Internal))
{
	exit((string) new RequestException(sprintf("Request %s:%s did not return an internal request.", strtoupper($_SERVER["REQUEST_METHOD"]), $_SERVER["PATH_INFO"])));
}
$request->setHeaders(apache_request_headers());
$request->setQueryData($_GET);
$request->setPostData($_POST);
$response = new Response;

try
{
	$url = explode("/", $_SERVER["PATH_INFO"]);
	array_shift($url);
	
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
	
	$response->send();
}
catch (HttpRedirectException $e)
{
	/**
	 * Handle the Redirect Exception and send the user off to the new location.
	 */
	$response = new Response;
	$response->header("Location", $e->getLocation());
	$response->status($e->getCode());
	$response->send();
}
catch (Exception $e)
{
	/**
	 * An uncaught exception means something bad happened.
	 */
	$response = new Response;
	$response->body((string)$e);
	$response->headers(array(
		"Exception" => get_class($e)
	));
	$response->status(500);
	$response->send();
}