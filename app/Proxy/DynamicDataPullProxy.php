<?php
namespace Vanderbilt\MyChartLookup\App\Proxy;

use Vanderbilt\MyChartLookup\MyChartLookup;

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
    public function __construct($project_id, $realtime_webservice_type, $module)
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
            $response = $this->module->fetchMyChartData($medical_record_number);
            $data = json_decode($response); //decode the response
            // alter the response array with data from the MyChart endpoint
            $mychart_status = $data->{MyChartLookup::MYCHART_STATUS_KEY} ?: false;
            $event_id = $this->module->getProjectSetting(MyChartLookup::EVENT_ID_KEY, $project_id);
            $my_chart_status_field = $this->module->getProjectSetting(MyChartLookup::MYCHART_STATUS_FIELD, $project_id);
            $redcap_field = $this->getMyChartRedCapField($record_id, $event_id, $my_chart_status_field, $mychart_status);
            $response_data_array[] = $redcap_field;
        }
        
        return array($response_data_array, $field_event_info);
    }

    /* public function getFhirData($record_identifier_external, $field_info)
    {
        $fhir_data = parent::getFhirData($record_identifier_external, $field_info);

        $response = $this->module->fetchMyChartData($record_identifier_external);
        $data = json_decode($response); //decode the response
        $mychart_data = array(
            'resourceType' => "MyChartLookup",
            'field' => MyChartLookup::MYCHART_STATUS_KEY,
            'value' => $data->{MyChartLookup::MYCHART_STATUS_KEY},
            'timestamp' => null,
        );
        $fhir_data->addData(array($mychart_data)); // new data must be added as array
        return $fhir_data;
    } */

    /**
     * override the list of mapped fields that
     * will be used to render the adjudication table (renderAdjudicationTable)
     * and addthe mapping for the MyChartLookup value
     * 
     * @return array
     */
    public function getMappedFields()
    {
        global $project_id;
        $event_id = $this->module->getProjectSetting(MyChartLookup::EVENT_ID_KEY, $project_id);
        $my_chart_status_field = $this->module->getProjectSetting(MyChartLookup::MYCHART_STATUS_FIELD, $project_id);
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
        $mapped_fields[MyChartLookup::MYCHART_STATUS_KEY] = $mychart_mapping;
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
            'field' => MyChartLookup::MYCHART_STATUS_KEY,
            'timestamp' =>  null,
            'value' => $numeric_string,
            'md_id' => $record_id,
            'event_id' => $event_id,
            'rcfield' => $field_name,
        );
        return $redcap_field;
    }

}