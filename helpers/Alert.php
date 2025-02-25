<?php

namespace TE\helpers;

use TE\core\App;

class Alert
{

    private static array $alert = [
        'g-danger' => [
            'class' => 'danger',
            'icon' => 'bi bi-x-lg',
        ],
        'g-success' => [
            'class' => 'success',
            'icon' => 'bi bi-check-lg',
        ],
        'g-info' => [
            'class' => 'info',
            'icon' => 'bi bi-info-circle',
        ],
        'g-warning' => [
            'class' => 'warning',
            'icon' => 'bi bi-exclamation-triangle',
        ],
    ];

    public static function run(): string
    {
        $js = "<script>";

        foreach (App::$session->alerts() as $count => $flash) {
            if (!array_key_exists($flash['type'], self::$alert)) {
                continue;
            }

            $jso = 'notify("' . $flash['msg'] . '", "' . self::$alert[$flash['type']]['class'] . '", "' . self::$alert[$flash['type']]['icon'] . '");';

            $js = $count > 0 ? 'setTimeout(function () {' . $jso . '}, ' . ($count * 500) . ');' : $jso;
        }

        return $js . "</script>";
    }

}