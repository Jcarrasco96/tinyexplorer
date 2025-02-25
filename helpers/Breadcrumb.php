<?php

namespace TE\helpers;

class Breadcrumb
{

    protected static function generateBreadcrumbElements(array $exploded, callable $urlGenerator): array
    {
        $breadcrumbElements = [];
        $parent = '';

        foreach ($exploded as $value) {
            $parent = trim($parent . '/' . $value, '/');
            if ($parent) {
                $breadcrumbElements[] = "<li class='breadcrumb-item'><a href='" . $urlGenerator($parent) . "'>" . Utils::enc($value) . "</a></li>";
            }
        }

        return $breadcrumbElements;
    }

    protected static function generateLastElement(?string $last): string
    {
        return $last ? "<li class='breadcrumb-item active' aria-current='page'>" . Utils::enc($last) . "</li>" : '';
    }

    protected static function generateBreadcrumbHtml(array $breadcrumbElements, string $homeUrl, string $lastElement): string
    {
        return "<nav aria-label='breadcrumb' class='flex-grow-1'>
            <ol class='breadcrumb'>
                <li class='breadcrumb-item'><a href='" . $homeUrl . "'><i class='bi bi-house-fill' aria-hidden='true'></i></a></li>
                " . implode('', $breadcrumbElements) . "
                $lastElement
            </ol>
        </nav>";
    }

    public static function run(array $config): string
    {
        $exploded = explode('/', $config['path']);
        $last = count($exploded) >= 1 ? array_pop($exploded) : null;

        $breadcrumbElements = self::generateBreadcrumbElements($exploded, function ($parent) {
            return Utils::urlTo("site/index/" . base64_encode($parent));
        });

        $lastElement = self::generateLastElement($last);

        return self::generateBreadcrumbHtml($breadcrumbElements, Utils::urlTo('site/index'), $lastElement);
    }

}