<?php

namespace TE\core;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use TE\database\Database;
use TE\models\System;
use TE\services\FileSystem;
use TE\services\Language;
use TE\services\Logger;
use TE\services\Session;

class App
{

    public static array $config = [
        'name' => 'TinyExplorer',
        'version' => '1.4',
    ];

    public static Database $database;
    public static Logger $logger;
    public static Session $session;
    public static System $system;
    public static Language $language;
    private Router $router;

    private float|string $time_start;

    public function __construct($config = [])
    {
        $this->time_start = microtime(true);

        define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
        define('VIEWS', ROOT . 'views' . DIRECTORY_SEPARATOR);
        define('LANGUAGES', ROOT . 'languages' . DIRECTORY_SEPARATOR);

        self::$config = array_merge(self::$config, $config);
        self::$database = new Database();
        self::$logger = new Logger();
        self::$session = new Session();
        self::$system = new System();

        self::$language = new Language(self::$system->language);

        $this->router = new Router();
    }

    /**
     * @throws Exception
     */
    #[NoReturn] public function run(): void
    {
        $this->router->run();
        $this->dispose();
        exit();
    }

    public function get($regex, $action): void
    {
        $this->router->addRoute($regex, $action, Router::ROUTER_GET);
    }

    public function post($regex, $action): void
    {
        $this->router->addRoute($regex, $action, Router::ROUTER_POST);
    }

//    public function put($regex, $action): void
//    {
//        $this->router->addRoute($regex, $action, Router::ROUTER_PUT);
//    }

//    public function delete($regex, $action): void
//    {
//        $this->router->addRoute($regex, $action, Router::ROUTER_DELETE);
//    }

    public static function t(string $key, array $params = []): string
    {
        return self::$language->t($key, $params);
    }

    private function dispose(): void
    {
        $mPeak = FileSystem::filesize(memory_get_peak_usage(true));
        $mUsage = FileSystem::filesize(memory_get_usage(true));

        $execTime = number_format(microtime(true) - $this->time_start, 4);

        self::$logger->notice("SCRIPT REAL EXECUTION TIME: $execTime, MEM PEAK USAGE: $mPeak, USAGE: $mUsage");
    }

}