<?php

namespace app\helpers;

use app\core\App;

class Alert
{

    private static array $alertTypes = [
        'g-danger' => 'danger',
        'g-success' => 'success',
        'g-info' => 'info',
        'g-warning' => 'warning',
    ];

    private static array $icons = [
        'success' => 'bi bi-check-lg',
        'danger' => 'bi bi-x-lg',
        'info' => 'bi bi-info-circle',
        'warning' => 'bi bi-exclamation-triangle',
    ];

    public static function run(): string
    {
        $count = 0;

        $js = "<script>";

        foreach (App::$session->alerts() as $flash) {
            if (!array_key_exists($flash['type'], self::$alertTypes)) {
                continue;
            }

            $type = self::$alertTypes[$flash['type']];

            $delay = 1000 * $count++;

            $jso = 'notify("' . $flash['msg'] . '", "' . $type . '", "' . self::$icons[$type] . '");';

            if (!empty($delay) && $delay > 0) {
                $js .= 'setTimeout(function () {' . $jso . '}, ' . $delay . ');';
            } else {
                $js .= $jso;
            }
        }

        $js .= "</script>";

        return $js;
    }

}