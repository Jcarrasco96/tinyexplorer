<?php

namespace app\core;

use app\models\System;
use app\services\Language;
use app\services\Logger;
use app\services\Router;
use app\services\RouterRequestMethod;
use app\services\Session;
use Exception;

class App
{

    public static array $config = [];

    public static Logger $logger;
    public static Session $session;
    public static System $system;
    public static Language $language;
    private Router $router;

    public function __construct($config = [])
    {
        define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
        define('APP_PATH', ROOT . 'app' . DIRECTORY_SEPARATOR);
        define('VIEWS', APP_PATH . 'views' . DIRECTORY_SEPARATOR);
        define('LANGUAGES', APP_PATH . 'languages' . DIRECTORY_SEPARATOR);

        self::$config = array_merge(self::$config, $config);
        self::$logger = new Logger();
        self::$session = new Session();
        self::$system = new System();
        self::$language = new Language(self::$system->language);

        $this->router = new Router();
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->router->run();
    }

    public function get($regex, $action): void
    {
        $this->router->addRoute($regex, $action, RouterRequestMethod::ROUTER_GET);
    }

    public function post($regex, $action): void
    {
        $this->router->addRoute($regex, $action, RouterRequestMethod::ROUTER_POST);
    }

    public function put($regex, $action): void
    {
        $this->router->addRoute($regex, $action, RouterRequestMethod::ROUTER_PUT);
    }

    public function delete($regex, $action): void
    {
        $this->router->addRoute($regex, $action, RouterRequestMethod::ROUTER_DELETE);
    }

    public static function t(string $key, array $params = []): string
    {
        return self::$language->t($key, $params);
    }

}