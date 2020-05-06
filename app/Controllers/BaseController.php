<?php
namespace Vanderbilt\MyChartLookup\App\Controllers;

class BaseController
{
	
	function __construct()
    {
        // enable cors
		$this->cors();
    }

	/**
	 *  CORS-compliant method.
	 *  It will allow any GET, POST, OPTIONS, PUT, PATCH, HEAD requests from any origin.
	 *
	 *  In a production environment, you probably want to be more restrictive.
	 *  For more read:
	 *
	 *  - https://developer.mozilla.org/en/HTTP_access_control
	 *  - http://www.w3.org/TR/cors/
	 *
	 */
	protected function cors() {

		// Allow from any origin
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
			// you want to allow, and if so:
			header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Max-Age: 86400');    // cache for 1 day
		}

		// Access-Control headers are received during OPTIONS requests
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
				header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, HEAD");         

			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
				header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

			exit(0);
		}

		// echo "You have CORS!";
	}

	function test()
	{
		$response = array(
			"message" => "this is just a test",
		);
		$this->printJSON($response);
	}

	// error 404
	function notFound()
	{
		header("HTTP/1.0 404 Not Found");
		$response = array(
			"error" => true,
			"message" => "page not found",
		);
		$this->printJSON($response);
	}

	// error 400
	function badRequest()
	{
		header("HTTP/1.0 400 Bad request");
		$response = array(
			"error" => true,
			"message" => "bad request",
		);
		$this->printJSON($response);
	}

	// error 405
	function notAllowed()
	{
		header("HTTP/1.0 405 Method Not Allowed");
		$response = array(
			"error" => true,
			"message" => "method not allowed",
		);
		$this->printJSON($response);
	}

	// error 401
	function unauthorized()
	{
		header("HTTP/1.1 401 Unauthorized");
		$response = array(
			"error" => true,
			"message" => "not authorized",
		);
		$this->printJSON($response);
	}

	/**
	 * echo a JSON response and exit
	 *
	 * @param array $response
	 * @return void
	 */
	protected function printJSON($response)
	{
		header('Content-Type: application/json');
		echo json_encode( $response );
		exit;
	}

}