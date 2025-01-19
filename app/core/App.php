<?php

namespace app\core;

use app\models\System;
use app\services\Logger;
use app\services\Session;
use Exception;

class App
{

    public static array $config = [];

    private array $routes = [];

    public static Logger $logger;
    public static Session $session;
    public static System $system;

    public function __construct($config = [])
    {
        self::$config = array_merge(self::$config, $config);
        self::$logger = new Logger();
        self::$session = new Session();
        self::$system = new System();
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
        $routes = $this->routes[$method] ?? null;

        if (!$routes) {
            throw new Exception("Method $method not allowed in $path_info.", 400);
        }

        foreach ($routes as $regex => $action) {
            if (preg_match($regex, $path_info)) {
                $controller_name = 'app\\controllers\\' . ucfirst(array_shift($segments)) . 'Controller';
                (new $controller_name)->createAction($action, $segments);
                exit;
            }
        }

        throw new Exception("The requested resource was not found.", 404);
    }

    public function get($regex, $action): void
    {
        $this->routes['GET'][$regex] = $action;
    }

    public function post($regex, $action): void
    {
        $this->routes['POST'][$regex] = $action;
    }

    public function put($regex, $action): void
    {
        $this->routes['PUT'][$regex] = $action;
    }

    public function delete($regex, $action): void
    {
        $this->routes['DELETE'][$regex] = $action;
    }

}