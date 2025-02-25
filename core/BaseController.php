<?php

namespace TE\core;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionMethod;
use TE\helpers\Utils;

abstract class BaseController
{

    protected string $layout = 'main';

    /**
     * @throws Exception
     */
    protected function beforeAction(ReflectionMethod $method): void
    {
        $attributes = $method->getAttributes(ControllerPermission::class);

        if (empty($attributes)) {
            return;
        }

        $permissionsAttribute = $attributes[0]->newInstance();
        $requiredPermissions = $permissionsAttribute->permissions;

        if (empty($requiredPermissions)) {
            return;
        }

        if ($this->checkSpecialPermissions($requiredPermissions)) {
            return;
        }

        foreach ($requiredPermissions as $permission) {
            if (App::$session->getPermission($permission) && !App::$session->isGuest()) {
                return;
            }
        }

        throw new Exception(App::t('You do not have permission to access this page.'));
    }

    protected function checkSpecialPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            switch ($permission) {
                case '?':
                    if (App::$session->isGuest()) {
                        return true;
                    }
                    break;

                case '*':
                    return true;

                case '@':
                    if (!App::$session->isGuest()) {
                        return true;
                    }

                    $currentUrl = trim($_SERVER['PATH_INFO'] ?? '', '/');

                    if ($currentUrl === 'site/index') {
                        $this->redirect('auth/login');
                    }

                    $this->redirect('auth/login?redirect=' . urlencode($currentUrl));
            }
        }
        return false;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function createAction($methodName, $params = []): bool
    {
        $methodNameNormalized = $this->normalizeAction($methodName);

        if (method_exists($this, $methodNameNormalized)) {
            $method = new ReflectionMethod($this, $methodNameNormalized);

            if ($method->isPublic()) {
                session_start();

                $this->beforeAction($method);

                App::$session->checkAndUpdatePermissions();
                echo $method->invokeArgs($this, $params);
                return true;
            }
        }

        return false;
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
        $params['controllerName'] = str_replace(['te\\controllers\\', 'controller'], '', strtolower(get_class($this)));
        return Renderer::renderPartial($view, $params);
    }

    /**
     * @throws Exception
     */
    protected function render(string $view, array $params = []): string
    {
        $params['controllerName'] = str_replace(['te\\controllers\\', 'controller'], '', strtolower(get_class($this)));
        return Renderer::render($view, $params, $this->layout);
    }

    /**
     * @throws Exception
     */
    protected function asJson(array $params = []): string
    {
        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
            unset($params["statusCode"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        if ($jsonResponse === false) {
            throw new Exception(App::t('Internal error on the server. Contact the administrator.'), 500);
        }

        return $jsonResponse;
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