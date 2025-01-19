<?php

namespace app\core;

use app\utils\Utils;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class BaseController
{

    private Renderer $renderer;

    public function __construct()
    {
        $this->renderer = new Renderer();
    }

    /**
     * @throws ReflectionException
     */
    public function createAction($methodName, $params = []): bool
    {
        $methodNameNormalized = $this->normalizeAction($methodName);

        $method = new ReflectionMethod($this, $methodNameNormalized);
        if ($method->isPublic()) {
            echo $method->invokeArgs($this, $params);
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function renderPartial(string $view, array $params = []): string
    {
        extract($params);

        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        ob_start();

        $reflection = new ReflectionClass($this);
        $controllerName = $reflection->getShortName();
        $controllerName = strtolower(str_replace('Controller', '', $controllerName));

        include VIEWS . "$controllerName/$view.php";

        $output = ob_get_clean();

        return $output !== false ? $output : throw new Exception(App::t('Internal error on the server. Contact the administrator.'), 500);
    }

    /**
     * @throws Exception
     */
    protected function render(string $view, array $params = []): string
    {
        $reflection = new ReflectionClass($this);
        $controllerName = $reflection->getShortName();
        $controllerName = strtolower(str_replace('Controller', '', $controllerName));

        $params['controllerName'] = $controllerName;

        return $this->renderer->render($view, $params);
    }

    /**
     * @throws Exception
     */
    protected function asJson(array $params = []): false|string
    {
        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        if ($jsonResponse === false) {
            throw new Exception(App::t('Internal error on the server. Contact the administrator.'), 500);
        }

        echo $jsonResponse;
        exit();
    }

    private function normalizeAction($methodName): ?string
    {
        return 'action' . ucfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $methodName))));
    }

    protected function isPost(): bool
    {
        return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function getJsonInput(): ?array
    {
        $input = file_get_contents('php://input');

        $data = json_decode($input, true);

        return is_array($data) ? $data : null;
    }

    #[NoReturn] public static function redirect(string $url, int $code = 302): string
    {
        header("Location: " . Utils::urlTo($url), true, $code);
        exit();
    }

}