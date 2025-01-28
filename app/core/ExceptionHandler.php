<?php

namespace app\core;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

class ExceptionHandler
{

    #[NoReturn] public static function handleException(Throwable $throwable): void
    {
        if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' && !headers_sent()) {
            header('HTTP/1.1 503 Service Unavailable');
        }

        $code = $throwable->getCode() == 0 ? 401 : $throwable->getCode();

        http_response_code($code);

        $params = [
            'code' => $code,
            'message' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
            'controllerName' => 'site',
            'pageTitle' => $code,
        ];

        @session_start();

        $msg = 'Uncaught ' . get_class($throwable) . ': ' . $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine() . PHP_EOL . 'Stack trace:' . PHP_EOL . $throwable->getTraceAsString();
        App::$logger->error($msg);

        try {
            $renderer = new Renderer();
            echo $renderer->render('error', $params);
        } catch (Exception) {
            echo $msg . PHP_EOL;
        }
    }

}