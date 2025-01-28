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
    protected string $layout = 'main';

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

        if (method_exists($this, $methodNameNormalized)) {
            $method = new ReflectionMethod($this, $methodNameNormalized);
            if ($method->isPublic()) {
                session_start();

                App::$session->checkAndUpdatePermissions();

                echo $method->invokeArgs($this, $params);
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    protected function ensureAuthenticated(bool $throwError = false): void
    {
        if (App::$session->isGuest()) {
            if ($throwError) {
                throw new Exception(App::t('You are not allowed to access this page.'));
            }
            $currentUrl = trim($_SERVER['PATH_INFO'] ?? '', '/');

            if ($currentUrl === 'site/index') {
                $this->redirect('auth/login');
            }

            $this->redirect('auth/login?redirect=' . urlencode($currentUrl));
        }
    }

    public function validateCsrf(string $redirect = 'site/index'): void
    {
        $data = $this->getPostData();

        $csrfToken = $data['_csrf_token'] ?? '';

        if (!App::$session->checkCSRF($csrfToken)) {
            App::$session->notify('g-danger', App::t('Invalid CSRF token.'));
            $this->redirect($redirect);
        }
    }

    /**
     * @throws Exception
     */
    protected function renderPartial(string $view, array $params = []): string
    {
        $reflection = new ReflectionClass($this);
        $controllerName = $reflection->getShortName();
        $controllerName = strtolower(str_replace('Controller', '', $controllerName));

        $params['controllerName'] = $controllerName;

        return $this->renderer->renderPartial($view, $params);
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

        return $this->renderer->render($view, $params, $this->layout);
    }

    /**
     * @throws Exception
     */
    protected function asJson(array $params = []): string
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

    protected function getPostData(): array
    {
        return $this->getJsonInput() ?? $_POST;
    }

    #[NoReturn] public static function redirect(string $url, int $code = 302): void
    {
        header("Location: " . Utils::urlTo($url), true, $code);
        exit();
    }

}