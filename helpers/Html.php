<?php

namespace TE\helpers;

class Html
{

    public static function css($css): string
    {
        return '<link href="' . Utils::urlTo('assets/css/' . $css) . '" rel="stylesheet">' . "\n";
    }

    public static function js($js): string
    {
        return '<script src="' . Utils::urlTo('assets/js/' . $js) . '"></script>' . "\n";
    }

    public static function icon($icon, $rel = 'icon'): string
    {
        return '<link href="' . Utils::urlTo('assets/' . $icon) . '" rel="' . $rel . '">' . "\n";
    }

    public static function img($img): string
    {
        return Utils::urlTo('assets/img/' . $img);
    }

    public static function uploadImg($img): string
    {
        return Utils::urlTo('uploads/' . $img);
    }

}