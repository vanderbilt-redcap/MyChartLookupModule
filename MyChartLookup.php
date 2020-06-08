<?php
// Set the namespace defined in your config file
namespace Vanderbilt\MyChartLookup;

use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;

$autoload = join([__DIR__,'vendor','autoload.php'],DIRECTORY_SEPARATOR);
if(file_exists($autoload)) require_once($autoload);

use Vanderbilt\MyChartLookup\App\Proxy\DynamicDataPullProxy;
use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;

class MyChartLookup extends \ExternalModules\AbstractExternalModule
{

    /**
     * key of the resource as is returned from the FHIR endpoint
     */
    const MYCHART_STATUS_KEY = 'MyChartStatus';
    
    /**
     * key of the settings for the event ID
     */
    const EVENT_ID_KEY = 'event-id';

    /**
     * key of the setting with the mapped field
     */
    const MYCHART_STATUS_FIELD = 'mychart-status-field';
    const MRN_FIELD = 'mrn-field';

    /**
     * get base URL for the API endpoint of the module
     *
     * @return void
     */
    public function getBaseUrl()
    {
        global $project_id;
        // https://redcap.test/redcap_v999.0.0/ExternalModules/?prefix=lookup_patient_and_mychart_account&page=test&pid=18
        if(!defined('APP_URL_EXTMOD')) throw new \Exception("Cannot compute the base URL: APP_URL_EXTMOD is not defined. ", 1);
        $base_url = APP_URL_EXTMOD;
        $query_params = array(
            'prefix' => $this->PREFIX,
        );
        if($project_id) $query_params['pid'] = $project_id;
        $query = http_build_query($query_params);
        $base_url .= "?{$query}";
        return $base_url;
    }

    /**
     * print text in html format
     *
     * @param [type] $text
     * @return void
     */
    private function debugPrint($text) {
        return;
        printf('<p style="color:red;font-weight:bold;">%s</p>', $text);
    }
    
    /**
     * REDCAP_EVERY_PAGE_BEFORE_RENDER
     * intercept the DDP instance before fetching data
     * and replace it with a proxy that injects
     * data from the LookupPatientAndMyChartAccount
     *
     * @return void
     */
    function redcap_every_page_before_render($project_id=null)  {
        global $DDP, $project_id, $realtime_webservice_type;
        try {
            $page = PAGE ?: false;
            switch ($page) {
                case 'DynamicDataPull/fetch.php':
                    // check if we are using a FHIR enabled project
                    if($realtime_webservice_type != "FHIR") throw new \Exception("This service is only available for FHIR endpoints", 1); // exit if not FHIR
                    // use a proxy to intercept the MRN and run custom code. also pass a reference to the module
                    $ddp_proxy = new DynamicDataPullProxy($project_id, $realtime_webservice_type, $module=$this);
                    $DDP = $ddp_proxy;
                    break;
                
                case 'DataMartController:runRevision':
                    $params = (object)$_POST;
                    $mrn = $params->mrn ?: false;
                    if(!$mrn) throw new \Exception("Please provide an MRN to check the MyChart status", 400);
                    $response = $this->fetchMyChartData($mrn);
                    $data = json_decode($response); //decode the response
                    $record_id = $this->findRecordId($project_id, $mrn);
                    if(empty($record_id))  throw new \Exception("Cannot save MyChart status: no record ID found", 400);
                    $this->setMyChartStatus($project_id, $record_id, $data);
                    break;
                
                default:
                    break;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            \Logging::logEvent( "", "mychart_lookup", "OTHER", $display=null, $code, $message );
        }
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
        $token_manager = new FhirTokenManager($user_id);
        $access_token = $token_manager->getAccessToken();
        $lookup_mychart_endpoint = new LookupPatientAndMyChartAccountEndpoint($fhir_endpoint_base_url);
        $response = $lookup_mychart_endpoint->check($access_token, $fhir_client_id, $medical_record_number);
        return $response;
    }

    /**
     * save mychart data in the designed field of the record
     *
     * @param string $record_id
     * @param object $data
     * @return array Records::saveData result
     */
    public function setMyChartStatus($project_id, $record_id, $data)
    {
        $event_id = $this->getProjectSetting(self::EVENT_ID_KEY, $project_id);
        $my_chart_status_field = $this->getProjectSetting(self::MYCHART_STATUS_FIELD, $project_id);
        // check if settings are correct
        if(empty($event_id)) throw new \Exception("Event ID has not been setup.", 1);
        if(empty($my_chart_status_field)) throw new \Exception("MyChart field has not been setup", 1);
        // Init data array
        $mychart_status = $data->{self::MYCHART_STATUS_KEY};
        $record_data = array($event_id => array(
            $my_chart_status_field => $mychart_status,
        ));
        $record = array($record_id=>$record_data);
        $save_response = \Records::saveData($project_id, 'array', $record);
        return $save_response;
    }

    /**
     * update the status for all records in the project
     *
     * @return void
     */
    function batchUpdate()
    {
        global $project_id, $userid, $fhir_endpoint_base_url, $fhir_client_id;

        $mrn_field_name = $this->getProjectSetting(MyChartLookup::MRN_FIELD, $project_id);
        $records_data = \Records::getData($project_id, 'array', $records=[],$fields=[$mrn_field_name]);

        $results = [];
        foreach ($records_data as $record_id => $event) {
            $record_data = reset($event); // extract data from the first event
            $mrn = $record_data[$mrn_field_name];
            $response = $this->fetchMyChartData($mrn);
            $data = json_decode($response); //decode the response
            $results[$mrn] = $data;
            // $record_id = $this->findRecordId($project_id, $mrn);
            // if(empty($record_id))  throw new \Exception("Cannot save MyChart status: no record ID found", 400);
            $this->setMyChartStatus($project_id, $record_id, $data);
        }
        return $results;
    }

    public function findRecordId($project_id, $mrn)
    {
        $event_id = $this->getProjectSetting(self::EVENT_ID_KEY, $project_id);
        $query_string = sprintf("SELECT record, value FROM redcap_data
                                    WHERE value='%s'
                                    AND field_name='mrn'
                                    AND event_id=%d
                                    AND project_id=%s LIMIT 1",
                                    db_real_escape_string($mrn),
                                    db_real_escape_string($event_id),
                                    db_real_escape_string($project_id));

        $result = db_query($query_string);
        if($row = db_fetch_assoc($result)) return $row['record'];
        return false;
    }
    
   
}