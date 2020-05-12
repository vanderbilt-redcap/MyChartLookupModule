<?php
// Set the namespace defined in your config file
namespace Vanderbilt\MyChartLookup;

$autoload = join([__DIR__,'vendor','autoload.php'],DIRECTORY_SEPARATOR);
if(file_exists($autoload)) require_once($autoload);

use Vanderbilt\MyChartLookup\App\Proxy\DynamicDataPullProxy;

class MyChartLookup extends \ExternalModules\AbstractExternalModule
{

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
     * REDCAP_CONTROL_CENTER
     *
     * @return void
     */
    function redcap_control_center() {}
    
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
            // if($page=='DynamicDataPull/preview.php') {
            if($page=='DynamicDataPull/fetch.php') {
                // check if we are using a FHIR enabled project
                if($realtime_webservice_type != "FHIR") throw new \Exception("This service is only available for FHIR endpoints", 1); // exit if not FHIR
                // use a proxy to intercept the MRN and run custom code. also pass a reference to the module
                $ddp_proxy = new DynamicDataPullProxy($project_id, $realtime_webservice_type, $module=$this);
                $DDP = $ddp_proxy;
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            \Logging::logEvent( "", "mychart_lookup", "OTHER", $display=null, $code, $message );
        }
    }
    
   
}