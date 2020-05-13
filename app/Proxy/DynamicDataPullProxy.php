<?php
namespace Vanderbilt\MyChartLookup\App\Proxy
{
    use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;

    class DynamicDataPullProxy extends \DynamicDataPull
    {

        /**
         * key of the resource as is returned from the FHIR endpoint
         *
         * @var string
         */
        private static $mychart_lookup_key = 'MyChartStatus';

        const MYCHART_STATUS_FIELD = 'mychart-status-field';

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
         * override
         * 
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

            // override the response array
            if($medical_record_number && $record_id) {
                // check MyChart endpoint and save data
                $response = $this->fetchMyChartData($medical_record_number);
                $data = json_decode($response); //decode the response
                // alter the response array with data from the MyChart endpoint
                $mychart_status = $data->{self::$mychart_lookup_key} ?: false;
                $event_id = $this->module->getProjectSetting('event-id', $project_id);
                $my_chart_status_field = $this->module->getProjectSetting(self::MYCHART_STATUS_FIELD, $project_id);
                $redcap_field = $this->getMyChartRedCapField($record_id, $event_id, $my_chart_status_field, $mychart_status);
                $response_data_array[] = $redcap_field;
            }
            
            return array($response_data_array, $field_event_info);
        }

        /* public function getFhirData($record_identifier_external, $field_info)
        {
            $fhir_data = parent::getFhirData($record_identifier_external, $field_info);

            $response = $this->fetchMyChartData($record_identifier_external);
            $data = json_decode($response); //decode the response
            $mychart_data = array(
                'resourceType' => "MyChartLookup",
                'field' => self::$mychart_lookup_key,
                'value' => $data->{self::$mychart_lookup_key},
                'timestamp' => null,
            );
            $fhir_data->addData(array($mychart_data)); // new data must be added as array
            return $fhir_data;
        } */

        /**
         * save mychart data in the designed field of the record
         *
         * @param string $record_id
         * @param object $data
         * @return array Records::saveData result
         */
        public function setMyChartStatus($record_id, $data)
        {
            global $project_id;
            $module = $this->module;
            $event_id = $module->getProjectSetting('event-id', $project_id);
            $my_chart_status_field = $module->getProjectSetting(self::MYCHART_STATUS_FIELD, $project_id);
            // check if settings are correct
            if(empty($event_id)) throw new \Exception("Event ID has not been setup.", 1);
            if(empty($my_chart_status_field)) throw new \Exception("MyChart field has not been setup", 1);
            // Init data array
            $mychart_status = $data->{self::$mychart_lookup_key};
			$record_data = array($event_id => array(
                $my_chart_status_field => $mychart_status,
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

        /**
         * override the list of mapped fields that
         * wil be used to render the adjudication table (renderAdjudicationTable)
         * and addthe mapping for the MyChartLookup value
         * 
         * @return array
         */
        public function getMappedFields()
        {
            global $project_id;
            $event_id = $this->module->getProjectSetting('event-id', $project_id);
            $my_chart_status_field = $this->module->getProjectSetting(self::MYCHART_STATUS_FIELD, $project_id);
            // create the mychart mapping
            $mychart_mapping = array(
                $event_id => array(
                    $my_chart_status_field => array(
                        'map_id' => null,
                        'is_record_identifier' => null,
                        'temporal_field' => null,
                        'preselect' => null,
                    )
                )
            );
            // get the original mappings and add the mychart mapping
            $mapped_fields = parent::getMappedFields();
            $mapped_fields[self::$mychart_lookup_key] = $mychart_mapping;
            return $mapped_fields;
        }

        /**
         * get the data structure that must be used in 
         * the response_array returned by fetchData 
         * 
         * @param string $record_id
         * @param string $event_id
         * @param string $field_name
         * @param string $value
         * @return void
         */
        private function getMyChartRedCapField($record_id, $event_id, $field_name, $value)
        {
            $boolean_value = boolval($value); //convert to boolean
            $numeric_string = strval(intval($boolean_value)); //convert to '0' or '1'
            $redcap_field = array(
                'field' => self::$mychart_lookup_key,
                'timestamp' =>  null,
                'value' => $numeric_string,
                'md_id' => $record_id,
                'event_id' => $event_id,
                'rcfield' => $field_name,
            );
            return $redcap_field;
        }

    }
}