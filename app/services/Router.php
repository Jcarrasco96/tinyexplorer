<?php

namespace app\services;

use app\core\App;
use app\core\BaseController;
use Exception;

class Router
{

    private array $routes = [];

    public function addRoute($regex, $action, RouterRequestMethod $method): void
    {
        $this->routes[$method->value][$regex] = $action;
    }

    public function routes(RouterRequestMethod $method): array
    {
        return $this->routes[$method->value] ?? [];
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $path_info = trim($_SERVER['PATH_INFO'] ?? '', '/');

        $segments = explode('/', $path_info);

        if (empty($segments[0])) {
            BaseController::redirect('auth/login');
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $routes = $this->routes(RouterRequestMethod::tryFrom($method));

        if (!$routes) {
            throw new Exception(App::t('Method {method} not allowed in {path_info}.', [$method, $path_info]), 400);
        }

        foreach ($routes as $regex => $action) {
            if (preg_match($regex, $path_info, $matches)) {
                array_shift($matches);

                $controller_name = 'app\\controllers\\' . ucfirst(explode('/', $path_info)[0]) . 'Controller';
                (new $controller_name)->createAction($action, $matches);
                exit;
            }
        }

        throw new Exception(App::t('The requested resource was not found.'), 404);
    }

}