<?php
namespace Vanderbilt\MyChartLookup\App;

use Vanderbilt\MyChartLookup\App\Controllers\BaseController;
use Vanderbilt\MyChartLookup\App\Helpers\Router;

require_once __DIR__."/bootstrap.php";

// httpMethod, route, handler
$routes = [
    // pages
    ['GET', "/test", 'Vanderbilt\MyChartLookup\App\Controllers\EndpointsController/test'],
    ['POST', "/LookupPatientAndMyChartAccount", 'Vanderbilt\MyChartLookup\App\Controllers\EndpointsController/LookupPatientAndMyChartAccount'],
    ['PUT', "/updateAll", 'Vanderbilt\MyChartLookup\App\Controllers\EndpointsController/updateAll'],
];

// create a BaseController to manage common routes or errors
$baseController = new BaseController();

$router = new Router($routes, $baseController);
$route_key = 'route'; // key used to identify a route in the URL query
$route = Router::extractRoute($route_key);

$router->dispatch($route);