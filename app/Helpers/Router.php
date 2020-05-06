<?php
namespace Vanderbilt\MyChartLookup\App\Helpers;

class Router {

    /**
     * list of routes
     */
    private $routes = array();
    
    function __construct($routes=[], $baseController)
    {
        $this->routes = $routes;
        $this->baseController = $baseController;
        $this->dispatcher = self::getDispatcher($routes);
    }

    /**
     * create a dispatcher and register the routes
     * each route is managed by a controller and one of it's functions.
     */
    private static function getDispatcher($routes)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use($routes) {
            foreach($routes as $route)
            {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        });
        return $dispatcher;
    }

    /**
     * extract the route from the request uri.
     * If $get_param is defined get the route
     * from the corresponding $_GET param
     *
     * @param string $get_param
     * @return string
     */
    static function extractRoute($get_param=null)
    {
        if(isset($get_param))
        {
            $route = isset($_GET[$get_param]) ? $_GET[$get_param] : '';
        }else {
            $uri = $_SERVER['REQUEST_URI'];
            
            // Strip query string (?foo=bar) and decode URI
            if (false !== $pos = strpos($uri, '?')) {
                $uri = substr($uri, 0, $pos);
            }
            $module_dir = basename(dirname(__DIR__));
            $re = '/.*'.preg_quote($module_dir).'\/api(\/.*)/';
            $route = preg_replace($re,'$1',$uri);
        }
        return rawurldecode($route);
    }

    function dispatch($route)
    {
        // get current fetch method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        // dispatch the current route
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $route);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $this->baseController->notFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                $this->baseController->notAllowed();
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // ... call $handler with $vars
                list($class, $method) = explode("/", $handler, 2);
                call_user_func_array(array(new $class, $method), $vars, );
                break;
        }
    }


    /**
     * can be useful for debugging purposes
     */
    private static function getRequestedURI()
    {
        $server = $_SERVER;
        // store request link for testing
        $request_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $request_link;
    }
}