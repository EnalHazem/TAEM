<?php
namespace AncientEgyptianMuseum\Http;

use AncientEgyptianMuseum\View\View;

/**
 * Route Class
 * Handles the application routing system
 */
class Route
{
    /**
     * @var Request
     */
    protected Request $request;
    
    /**
     * @var Response
     */
    protected Response $response;
    
    /**
     * @var array
     */
    protected static array $routes = [];
    
    /**
     * Constructor
     * 
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Register a GET route
     * 
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public static function get($route, $action)
    {
        self::$routes['get'][$route] = $action;
    }
    
    /**
     * Register a POST route
     * 
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public static function post($route, $action)
    {
        self::$routes['post'][$route] = $action;
    }
    
    /**
     * Register a PUT route
     * 
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public static function put($route, $action)
    {
        self::$routes['put'][$route] = $action;
    }
    
    /**
     * Register a DELETE route
     * 
     * @param string $route
     * @param mixed $action
     * @return void
     */
    public static function delete($route, $action)
    {
        self::$routes['delete'][$route] = $action;
    }
    
    /**
     * Resolve the current route
     * 
     * @return mixed
     */
    public function resolve()
    {
        $path = $this->request->path();
        $method = strtolower($this->request->method());
        
        // Check if the method exists in routes
        if (!isset(self::$routes[$method])) {
            $this->response->setStatusCode(405);
            return View::makeError('405');
        }
        
        // Check if the path exists in the current method routes
        if (!isset(self::$routes[$method][$path])) {
            $this->response->setStatusCode(404);
            return View::makeError('404');
        }
        
        $action = self::$routes[$method][$path];
        
        // Handle the action
        if (is_callable($action)) {
            return call_user_func_array($action, [$this->request, $this->response]);
        }
        
        if (is_array($action)) {
            [$controller, $method] = $action;
            
            if (!class_exists($controller)) {
                $this->response->setStatusCode(500);
                return View::makeError('500');
            }
            
            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                $this->response->setStatusCode(500);
                return View::makeError('500');
            }
            
            return call_user_func_array([$controllerInstance, $method], [$this->request, $this->response]);
        }
        
        $this->response->setStatusCode(500);
        return View::makeError('500');
    }
    
    /**
     * Get all registered routes
     * 
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }
}

/*
namespace SecTheater\Http;

use SecTheater\View\View;

class Route
{
    protected Request $request;
    protected Response $response;

    protected static array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public static function get($route, $action)
    {
        self::$routes['get'][$route] = $action;
    }

    public static function post($route, $action)
    {
        self::$routes['post'][$route] = $action;
    }

    public function resolve()
    {
        $path = $this->request->path();
        $method = $this->request->method();
        $action = self::$routes[$method][$path] ?? false;

        if (!array_key_exists($path, self::$routes[$method])) {
            $this->response->setStatusCode(404);
            View::makeError('404');
        }

        if (!$action) {
            return;
        }

        if (is_callable($action)) {
            call_user_func_array($action, []);
        }

        if (is_array($action)) {
            $controller = new $action[0];
            $method = $action[1];

            call_user_func_array([$controller, $method], []);
        }
    }
}
*/