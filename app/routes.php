<?php
namespace Vanderbilt\MyChartLookup\App;

use Vanderbilt\MyChartLookup\App\Controllers\BaseController;
use Vanderbilt\MyChartLookup\App\Helpers\Router;

require_once __DIR__."/bootstrap.php";

$protected = true;
// httpMethod, route, handler
$routes = [
    // pages
    ['GET', "/", 'Biondo\OperaRegistri\App\Controllers\PageController/showHome', $protected],
    ['GET', "/about", 'Biondo\OperaRegistri\App\Controllers\PageController/showAbout'],
    ['GET', "/dump", 'Biondo\OperaRegistri\App\Controllers\ToolController/dump'],
    // registri
    ['GET', "/api/registri[/]", 'Biondo\OperaRegistri\App\Controllers\RegistroController/list'],
    ['GET', "/api/registri/{id:\d+}", 'Biondo\OperaRegistri\App\Controllers\RegistroController/get'],
    ['GET', "/api/registri/{id:\d+}/carte", 'Biondo\OperaRegistri\App\Controllers\RegistroController/getCarte'],
    // carte
    ['GET', "/api/carte[/]", 'Biondo\OperaRegistri\App\Controllers\CartaController/list'],
    [['PUT', 'POST'], "/api/carte/{id:\d+}", 'Biondo\OperaRegistri\App\Controllers\CartaController/update', $protected],
    ['GET', "/api/carte/{id:\d+}", 'Biondo\OperaRegistri\App\Controllers\CartaController/get'],
    ['POST', "/api/carte/{id:\d+}/slide", 'Biondo\OperaRegistri\App\Controllers\CartaController/slide', $protected],
    // test
    ['GET', "/api/test[/{id:\d+}]", 'Biondo\OperaRegistri\App\Controllers\BaseController/test'],
    // user
    ['GET', "/register", 'Biondo\OperaRegistri\App\Controllers\PageController/showRegister'],
    // ['POST', "/register", 'Biondo\OperaRegistri\App\Controllers\UserController/register'], // make suer only admins can create a user
    ['GET', "/login", 'Biondo\OperaRegistri\App\Controllers\PageController/showLogin'],
    ['POST', "/login", 'Biondo\OperaRegistri\App\Controllers\UserController/login'],
    ['GET', "/logout", 'Biondo\OperaRegistri\App\Controllers\UserController/logout'],
];

// create a BaseController to manage common routes or errors
$baseController = new BaseController();

$router = new Router($routes, $baseController);
$route = Router::extractRoute();

$router->dispatch($route);