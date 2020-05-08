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
		$status_code = 404;
		$response = array(
			"error" => true,
			"message" => "page not found",
		);
		$this->printJSON($response, $status_code);
	}

	// error 400
	function badRequest()
	{
		$status_code = 400;
		$response = array(
			"error" => true,
			"message" => "bad request",
		);
		$this->printJSON($response, $status_code);
	}

	// error 405
	function notAllowed()
	{
		$status_code = 405;
		$response = array(
			"error" => true,
			"message" => "method not allowed",
		);
		$this->printJSON($response, $status_code);
	}

	// error 401
	function unauthorized()
	{
		$status_code = 401;
		$response = array(
			"error" => true,
			"message" => "not authorized",
		);
		$this->printJSON($response, $status_code);
	}

	/**
	 * print a JSON response and exit
	 * 
	 * some status codes for reference:
	 * 	100: 'Continue'
	 * 	101: 'Switching Protocols'
	 * 	200: 'OK'
	 * 	201: 'Created'
	 * 	202: 'Accepted'
	 * 	203: 'Non-Authoritative Information'
	 * 	204: 'No Content'
	 * 	205: 'Reset Content'
	 * 	206: 'Partial Content'
	 * 	300: 'Multiple Choices'
	 * 	301: 'Moved Permanently'
	 * 	302: 'Moved Temporarily'
	 * 	303: 'See Other'
	 * 	304: 'Not Modified'
	 * 	305: 'Use Proxy'
	 * 	400: 'Bad Request'
	 * 	401: 'Unauthorized'
	 * 	402: 'Payment Required'
	 * 	403: 'Forbidden'
	 * 	404: 'Not Found'
	 * 	405: 'Method Not Allowed'
	 * 	406: 'Not Acceptable'
	 * 	407: 'Proxy Authentication Required'
	 * 	408: 'Request Time-out'
	 * 	409: 'Conflict'
	 * 	410: 'Gone'
	 * 	411: 'Length Required'
	 * 	412: 'Precondition Failed'
	 * 	413: 'Request Entity Too Large'
	 * 	414: 'Request-URI Too Large'
	 * 	415: 'Unsupported Media Type'
	 * 	500: 'Internal Server Error'
	 * 	501: 'Not Implemented'
	 * 	502: 'Bad Gateway'
	 * 	503: 'Service Unavailable'
	 * 	504: 'Gateway Time-out'
	 * 	505: 'HTTP Version not supported'
	 *
	 * @param array $response
	 * @param integer $status_code
	 * @return void
	 */
	public static function printJSON($response, $status_code=200)
	{
		http_response_code($status_code); // set the status header
		header('Content-Type: application/json');
		print json_encode_rc( $response );
		exit;
	}

}