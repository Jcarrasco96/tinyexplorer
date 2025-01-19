<?php

namespace app\helpers;

use app\core\App;

class Html
{

    public static function css($css): string
    {
        $cssPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/assets/css/' . $css;

        return '<link href="' . $cssPath . '" rel="stylesheet">' . "\n";
    }

    public static function js($js): string
    {
        $jsPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/assets/js/' . $js;

        return '<script src="' . $jsPath . '"></script>' . "\n";
    }

    public static function icon($icon, $rel = 'icon'): string
    {
        $iconPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/assets/' . $icon;

        return '<link href="' . $iconPath . '" rel="' . $rel . '">' . "\n";
    }

    public static function img($img): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/assets/img/' . $img;
    }

    public static function uploadImg($img): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/uploads/' . $img;
    }

}