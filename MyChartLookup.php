<?php
// Set the namespace defined in your config file
namespace Vanderbilt\MyChartLookup;

$autoload = join([__DIR__,'vendor','autoload.php'],DIRECTORY_SEPARATOR);
if(file_exists($autoload)) require_once($autoload);

use Aws\Api\Serializer\QueryParamBuilder;
use Vanderbilt\MyChartLookup\App\Proxy\DynamicDataPullProxy;

class MyChartLookup extends \ExternalModules\AbstractExternalModule {

    /* public function __construct()
    {
        parent::__construct();
    } */

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
        // $this->debugPrint('redcap_every_page_before_render');
        $mrn_field = $this->getProjectSetting('mrn-field', $project_id);
        $has_mychart_field = $this->getProjectSetting('mychart-field', $project_id);
        $test = $realtime_webservice_type;
        $page = PAGE ?: false;
        if($page=='DynamicDataPull/fetch.php') {
            $ddp_proxy = new DynamicDataPullProxy($project_id, $realtime_webservice_type);
            $DDP = $ddp_proxy;
        }
    }
    
    /**
     * REDCAP_EVERY_PAGE_TOP
     *
     * @return void
     */
    /* function redcap_every_page_top($project_id=null)  {
        $this->debugPrint('redcap_every_page_top');
    } */
    
    /**
     * REDCAP_DATA_ENTRY_FORM
     *
     * @return void
     */
    /* function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        $this->debugPrint('redcap_data_entry_form');
    } */
    
    /**
     * REDCAP_DATA_ENTRY_FORM TOP
     *
     * @return void
     */
    /* function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        $this->debugPrint('redcap_data_entry_form_top');
    } */
    
    /**
     * REDCAP_SAVE_RECORD
     *
     * @return void
     */
    /* function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
        $this->debugPrint('redcap_save_record');
    } */
    
    /**
     * REDCAP_SURVEY_COMPLETE
     *
     * @return void
     */
    /* function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
        $this->debugPrint('redcap_survey_complete');
    } */
    
    /**
     * REDCAP_SURVEY_PAGE
     *
     * @return void
     */
    /* function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
        $this->debugPrint('redcap_survey_page');
    } */
    
    /**
     * REDCAP_SURVEY_PAGE TOP
     *
     * @return void
     */
    /* function redcap_survey_page_top($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
        $this->debugPrint('redcap_survey_page_top');
    } */
    
    /**
     * ADD/EDIT RECORDS PAGE
     *
     * @return void
     */
    /* function redcap_add_edit_records_page($project_id, $instrument, $event_id) {
        $this->debugPrint('redcap_add_edit_records_page');
    } */
    
    /**
     * REDCAP_USER_RIGHTS
     *
     * @return void
     */
    /* function redcap_user_rights($project_id) {
        $this->debugPrint('redcap_user_rights');
    } */
    
    /**
     * REDCAP_PROJECT_HOME_PAGE
     *
     * @return void
     */
    /* function redcap_project_home_page($project_id) {
        $this->debugPrint('redcap_project_home_page');
    } */
    
    /**
     * REDCAP_CUSTOM_VERIFY_USERNAME
     *
     * @return void
     */
    /* function redcap_custom_verify_username($user) {
        $this->debugPrint('redcap_custom_verify_username');
    } */
    
    /**
     * REDCAP_PDF
     *
     * @return void
     */
    /* function redcap_pdf($project_id, $metadata, $data, $instrument=null, $record=null, $event_id=null, $instance=1) {
        $this->debugPrint('redcap_pdf');
    } */
    
    /**
     * REDCAP_EMAIL
     *
     * @return void
     */
    /* function redcap_email($to, $from, $subject, $message, $cc, $bcc, $fromName, $attachments) {
        $this->debugPrint('redcap_email');
    } */

    /**
     * Extra hooks provided by External Modules
     */

    /**
     * Triggered when a module gets enabled on Control Center.
     *
     * @param string $version
     * @return void
     */
    /* function redcap_module_system_enable($version){
        $this->debugPrint('redcap_module_system_enable');
    } */

    /**
     * Triggered when a module gets disabled on Control Center.
     *
     * @param string $version
     * @return void
     */
    /* function redcap_module_system_disable($version){
        $this->debugPrint('redcap_module_system_disable');
    } */

    /**
     * Triggered when a module version is changed.
     *
     * @param string $version
     * @param string $old_version
     * @return void
     */
    /* function redcap_module_system_change_version($version, $old_version){
        $this->debugPrint('redcap_module_system_change_version');
    } */

    /**
     * Triggered when a module gets enabled on a specific project.
     *
     * @param string $version
     * @param integer $project_id
     * @return void
     */
    /* function redcap_module_project_enable($version, $project_id){
        $this->debugPrint('redcap_module_project_enable');
    } */

    /**
     * Triggered when a module gets disabled on a specific project.
     *
     * @param string $version
     * @param integer $project_id
     * @return void
     */
    /* function redcap_module_project_disable($version, $project_id){
        $this->debugPrint('redcap_module_project_disable');
    } */

    /**
     * Triggered when each enabled module defined is rendered. Return null if you don't want to display the Configure button and true to display.
     *
     * @param integer $project_id
     * @return boolean
     */
    /* function redcap_module_configure_button_display($project_id){
        return true;
    } */

    /**
     * Triggered when each link defined in config.json is rendered. Override this method and return null if you don't want to display the link, or modify and return the $link parameter as desired. $link is an array matching the values of the link from config.json. The 'url' value will already have the module prefix and page appended as GET parameters. This method also controls whether pages will load if users access their URLs directly.
     *
     * @param integer $project_id
     * @param string $link
     * @return void
     */
    /* function redcap_module_link_check_display($project_id, $link){
        $this->debugPrint('redcap_module_link_check_display');
    } */

    /**
     * Triggered after a module configuration is saved.
     *
     * @param integer $project_id
     * @return void
     */
    /* function redcap_module_save_configuration($project_id){
        $this->debugPrint('redcap_module_save_configuration');
    } */

    /**
     * Triggered at the top of the Data Import Tool page.
     *
     * @param integer $project_id
     * @return void
     */
    /* function redcap_module_import_page_top($project_id){
        $this->debugPrint('redcap_module_import_page_top');
    } */
}