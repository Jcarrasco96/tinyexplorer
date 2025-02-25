<?php

namespace TE\helpers;

class BreadcrumbCopy extends Breadcrumb
{

    public static function run(array $config): string
    {
        $exploded = explode('/', $config['path']);
        $last = count($exploded) >= 1 ? array_pop($exploded) : null;

        $breadcrumbElements = self::generateBreadcrumbElements($exploded, function ($parent) use ($config) {
            return Utils::urlTo("site/copy/" . $config['type'] . '/' . base64_encode($config['file']) . '?p=' . base64_encode($parent));
        });

        $lastElement = self::generateLastElement($last);

        return self::generateBreadcrumbHtml($breadcrumbElements, Utils::urlTo('site/copy/' . $config['type'] . '/' . base64_encode($config['file'])), $lastElement);
    }

}