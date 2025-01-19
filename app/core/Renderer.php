<?php

namespace app\core;

use Exception;

class Renderer
{

    private string $layout = 'main';

    /**
     * @throws Exception
     */
    public function render(string $view, array $params = []): string
    {
        $viewPath = VIEWS . "{$params['controllerName']}/$view.php";
        $layoutPath = VIEWS . 'layouts/' . $this->layout . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception(App::t('The view "{view}" does not exist.', [$viewPath]));
        }

        unset($params['controllerName']);

        extract($params);

        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        $pageTitle = $params['pageTitle'] ?? ucfirst($view);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }

}