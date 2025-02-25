<?php

namespace TE\core;

use Exception;

class Renderer
{

    public static function render(string $view, array $params = [], string $layout = 'main'): string
    {
        try {
            $pageTitle = $params['pageTitle'] ?? ucfirst($view);

            $content = self::renderView($view, $params);

            ob_start();
            require VIEWS . 'layouts/' . $layout . '.php';
            return ob_get_clean();
        } catch (Exception $e) {
            return "ERROR: " . $e->getMessage();
        }
    }

    public static function renderPartial(string $view, array $params): string
    {
        try {
            return self::renderView($view, $params);
        } catch (Exception $e) {
            return "ERROR: " . $e->getMessage();
        }
    }

    /**
     * @throws Exception
     */
    private static function renderView(string $view, array $params = []): string
    {
        $viewPath = VIEWS . "{$params['controllerName']}/$view.php";

        if (!file_exists($viewPath)) {
            throw new Exception(App::t('The view "{view}" does not exist.', [$viewPath]));
        }

        unset($params['controllerName']);

        extract($params);

        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        ob_start();
        require $viewPath;
        return ob_get_clean() ?: throw new Exception(App::t('Internal error on the server. Contact the administrator.'), 500);
    }

}