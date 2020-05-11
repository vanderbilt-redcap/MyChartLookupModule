<?php
namespace Vanderbilt\MyChartLookup\App\Proxy
{

    use Vanderbilt\MyChartLookup\MyChartLookup;
    use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;

    class DynamicDataPullProxy extends \DynamicDataPull
    {

        /**
         * reference to the module
         *
         * @var \ExternalModules\AbstractExternalModule
         */
        private $module;
        /**
         * constructor
         *
         * @param integer $project_id
         * @param string $realtime_webservice_type
         * @param \ExternalModules\AbstractExternalModule $module
         */
        public function __construct($project_id, $realtime_webservice_type,$module)
        {
            $this->module = $module;
            parent::__construct($project_id, $realtime_webservice_type);
        }

        
        /**
         * fetch data using the parent function then inject data from
         * the LookupPatientMyChartAccount endpoint
         */
        public function fetchData()
        {
            global $project_id;
            $function_arguments = func_get_args();
            list($response_data_array, $field_event_info) = call_user_func_array(array(__CLASS__, 'parent::fetchData'), $function_arguments);
            if(!is_array($response_data_array)) return false;

            $medical_record_number = $function_arguments[2] ?: false; // get MRN from arguments
            $record_id = $function_arguments[0] ?: false; // get record ID from arguments

            if($medical_record_number && $record_id) {
                // check MyChart endpoint and save data
                $response = $this->fetchMyChartData($medical_record_number);
                $data = json_decode($response); //decode the response
                $is_mychart_patient = $data->IsPatient ?: false;
                $event_id = $this->module->getProjectSetting('event-id', $project_id);
                $has_mychart_field = $this->module->getProjectSetting('has-mychart-field', $project_id);
                $redcap_field = $this->getHasMyChartRedCapField($record_id, $event_id, $has_mychart_field, $is_mychart_patient);
                $response_data_array[] = $redcap_field;
                // $this->setHasMyChart($record_id, $data);
            }
            
            return array($response_data_array, $field_event_info);
        }

        private function getHasMyChartRedCapField($record_id, $event_id, $field_name, $value)
        {
            $redcap_field = array(
                'field' => "IsPatient",
                'timestamp' =>  null,
                'value' => $value,
                'md_id' => $record_id,
                'event_id' => $event_id,
                'rcfield' => $field_name,
            );
            return $redcap_field;
        }

        /**
         * fetch MyChart data and save the response
         * in the designed field of the record
         *
         * @param string $medical_record_number
         * @param string $record_id
         * @return array Records::saveData result
         */
        public function setHasMyChart($record_id, $data)
        {
            global $project_id;
            $module = $this->module;
            $event_id = $module->getProjectSetting('event-id', $project_id);
            $mrn_field = $module->getProjectSetting('mrn-field', $project_id);
            $has_mychart_field = $module->getProjectSetting('has-mychart-field', $project_id);
            // check if settings are correct
            if(empty($event_id)) throw new \Exception("Event ID has not been setup.", 1);
            if(empty($mrn_field)) throw new \Exception("MRN field has not been setup.", 1); // do I need this? probably not
            if(empty($has_mychart_field)) throw new \Exception("MyChart field has not been setup", 1);
            // Init data array
            $has_my_chart = boolval($data->IsPatient);
			$record_data = array($event_id => array(
                $has_mychart_field => intval($has_my_chart),
            ));
            $record = array($record_id=>$record_data);
            $save_response = \Records::saveData($project_id, 'array', $record);
            return $save_response;
        }

        /**
         * fetch MyChart data
         *
         * @param string $medical_record_number
         * @throws \Exception if settings are not set
         * @return void
         */
        public function fetchMyChartData($medical_record_number)
        {
            global $user_id, $fhir_client_id, $fhir_endpoint_base_url;
            $token_manager = new \FhirTokenManager($user_id);
            $access_token = $token_manager->getAccessToken();
            $lookup_mychart_endpoint = new LookupPatientAndMyChartAccountEndpoint($fhir_endpoint_base_url);
            $response = $lookup_mychart_endpoint->check($access_token, $fhir_client_id, $medical_record_number);
            return $response;
        }

        
    }
}