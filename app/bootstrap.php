<?php
namespace Vanderbilt\MyChartLookup\App;
// load composer dependencies
$autoload = dirname(dirname(__FILE__))."/vendor/autoload.php";
require_once ($autoload);

// Disable REDCap's authentication
define("NOAUTH", true);

$redcap_root_directory = dirname(dirname(dirname(__DIR__)));

// Call the REDCap Connect file in the main "redcap" directory
require_once "{$redcap_root_directory}/redcap_connect.php";