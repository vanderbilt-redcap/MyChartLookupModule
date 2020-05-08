<?php
namespace Vanderbilt\MyChartLookup\App\Controllers;

use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;
use \FhirTokenManager;

class EndpointsController extends BaseController
{
	
	function __construct()
    {
	}
	
	public function test($message='123 test')
	{
		$success = true;
		$response = compact('message', 'success');
		$this->printJSON($response);
	}

	public function LookupPatientAndMyChartAccount()
	{
		global $fhir_endpoint_base_url, $fhir_client_id, $userid;
		$user_id = \User::getUIIDByUsername($userid);
		try {
			if(!isset($_POST['mrn']))throw new \Exception("No MRN provided", 1);
			$mrn = $_POST['mrn'];
			$token_manager = new FhirTokenManager($user_id);
			$access_token = $token_manager->getAccessToken();
			$endpoint = new LookupPatientAndMyChartAccountEndpoint($fhir_endpoint_base_url);
			$data = $endpoint->check($access_token,$fhir_client_id, $mrn);
			$this->printJSON(json_decode($data));
		} catch (\Exception $e) {
			$message = $e->getMessage();
			$status_code = $e->getCode();
			$response = compact('message', 'status_code');
			$this->printJSON($response, $status_code);
		}
	}

}