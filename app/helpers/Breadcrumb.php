<?php

namespace app\helpers;

use app\services\FileSystem;
use app\utils\Utils;

class Breadcrumb
{

    public static function run(array $config): string
    {
//        if (empty($config['path'])) {
//            return '';
//        }

        $exploded = explode('/', $config['path']);
        $breadcrumbElements = [];
        $parent = '';

        $last = null;
        if (count($exploded) >= 1) {
            $last = array_pop($exploded);
        }

        foreach ($exploded as $value) {
            $parent = trim($parent . '/' . $value, '/');

            if ($parent == '') {
                continue;
            }

            $breadcrumbElements[] = "<li class='breadcrumb-item'><a href='" . Utils::urlTo("site/index?p=" . base64_encode($parent)) . "'>" . Utils::enc(FileSystem::convertWin($value)) . "</a></li>";
        }

        if ($last) {
            $breadcrumbElements[] = "<li class='breadcrumb-item active' aria-current='page'>" . Utils::enc(FileSystem::convertWin($last)) . "</li>";
        }

        $html = "<nav aria-label='breadcrumb' class='flex-grow-1'>";
        $html .= "<ol class='breadcrumb'>";
        $html .= "<li class='breadcrumb-item'><a href='" . Utils::urlTo('site/index') . "'><i class='bi bi-house' aria-hidden='true'></i></a></li>";
        $html .= implode('', $breadcrumbElements);
        $html .= "</ol>";
        $html .= "</nav>";

        return $html;
    }

}