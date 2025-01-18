<?php

use JetBrains\PhpStorm\NoReturn;

ignore_user_abort(true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error/error_' . date('Ymd') . '.log');

#[NoReturn] function exception_handler($exception): void
{
    if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1' && !headers_sent()) {
        header('HTTP/1.1 503 Service Unavailable');
    }

    $code = $exception->getCode() == 0 ? 401 : $exception->getCode();

    http_response_code($code);

    header('Content-Type: application/json; charset=utf8');

    echo json_encode([
        'status'  => $code,
        'message' => $exception->getMessage(),
        'trace'   => $exception->getTraceAsString(),
    ]);
    exit;
}
