<?php
namespace Vanderbilt\MyChartLookup\App\Proxy
{

    use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;

    class DynamicDataPullProxy extends \DynamicDataPull
    {

        
        /**
         * fetch data using the parent function then inject data from
         * the LookupPatientMyChartAccount endpoint
         */
        public function fetchData()
        {
            $function_arguments = func_get_args();
            list($response_data_array, $field_event_info) = call_user_func_array(array(__CLASS__, 'parent::fetchData'), $function_arguments);
            if(!is_array($response_data_array)) return false;
            if($medical_record_number = $function_arguments[2]) {
                $data = $this->fetchMyChartData($medical_record_number);
                if($data) {
                    $response_data_array[] = json_decode($data, true);
                }
            }
            

            return array($response_data_array, $field_event_info);
        }

        public function fetchMyChartData($medical_record_number)
        {
            global $user_id, $fhir_client_id, $fhir_endpoint_base_url, $module;
            try {
                $token_manager = new \FhirTokenManager($user_id);
                $access_token = $token_manager->getAccessToken();
                $lookup_mychart_endpoint = new LookupPatientAndMyChartAccountEndpoint($fhir_endpoint_base_url);
                $data = $lookup_mychart_endpoint->check($access_token, $fhir_client_id, $medical_record_number);
                $test = $module;
                return $data;
            } catch (\Exception $e) {
                return false;
            }
        }

        
    }
}