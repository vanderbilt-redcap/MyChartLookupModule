<?php
namespace Vanderbilt\MyChartLookup\App;
// load composer dependencies
require_once dirname(dirname(__FILE__))."/vendor/autoload.php";

// Disable REDCap's authentication
define("NOAUTH", true);

$redcap_root_directory = dirname(dirname(dirname(__DIR__)));

// Call the REDCap Connect file in the main "redcap" directory
require_once "{$redcap_root_directory}/redcap_connect.php";