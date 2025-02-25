<?php

namespace TE\core;

use Exception;

class Router
{

//    const ROUTER_DELETE = 'DELETE';
    const ROUTER_GET = 'GET';
//    const ROUTER_HEAD = 'HEAD';
//    const ROUTER_OPTIONS = 'OPTIONS';
//    const ROUTER_PATCH = 'PATCH';
    const ROUTER_POST = 'POST';
//    const ROUTER_PUT = 'PUT';

    private array $routes = [];

    public function addRoute($regex, $action, string $method): void
    {
        if (in_array($method, [
//            self::ROUTER_DELETE,
            self::ROUTER_GET,
//            self::ROUTER_HEAD,
//            self::ROUTER_OPTIONS,
//            self::ROUTER_PATCH,
            self::ROUTER_POST,
//            self::ROUTER_PUT,
        ])) {
            $this->routes[$method][$regex] = $action;
        }
    }

    public function routes(string $method): array
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $path_info = trim($_SERVER['PATH_INFO'] ?? '', '/');

        $segments = explode('/', $path_info);

        if (empty($segments[0])) {
            BaseController::redirect('site/index');
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $routes = $this->routes($method);

        if (!$routes) {
            throw new Exception(App::t('Method {method} not allowed in {path_info}.', [$method, $path_info]), 400);
        }

        foreach ($routes as $regex => $action) {
            if (preg_match($regex, $path_info, $matches)) {
                $controller_name = 'TE\\controllers\\' . ucfirst(explode('/', $path_info)[0]) . 'Controller';
                (new $controller_name)->createAction($action, array_slice($matches, 1));
                return;
            }
        }

        throw new Exception(App::t('The requested resource was not found.'), 404);
    }

}