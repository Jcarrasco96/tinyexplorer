<?php

namespace app\core;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class BaseController
{


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
    protected function render(string $view, array $params = []): string
    {
        extract($params);

        if (isset($params["status_code"])) {
            http_response_code($params["status_code"]);
        }

        ob_start();

        $reflection = new ReflectionClass($this);
        $controllerName = $reflection->getShortName();
        $controllerName = strtolower(str_replace('Controller', '', $controllerName));

        include VIEWS . "$controllerName/$view.php";

        $output = ob_get_clean();

        return $output !== false ? $output : throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
    }

    /**
     * @throws Exception
     */
    protected function asJson(array $params = []): false|string
    {
        if (isset($params["status_code"])) {
            http_response_code($params["status_code"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        return $jsonResponse !== false ? $jsonResponse : throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
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

    #[NoReturn] public function redirect(string $url): string
    {
        header("Location: " . Utils::urlTo($url), true, 302);
        return '';
    }

}