<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\models\User;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class AuthController extends BaseController
{

    /**
     * @throws Exception
     */
    public function actionLogin(): string
    {
        session_start();

        if (App::$session->isLoggedIn()) {
            return $this->redirect('site/index');
        }

        $error = [];

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            $csrfToken = $data['_csrf_token'] ?? '';

            if (!App::$session->checkCSRF($csrfToken)) {
                throw new Exception(App::t('Invalid CSRF token.'));
            }

            if (empty($data["email"])) {
                $error['email'] = App::t('Email is required.');
            }

            if (empty($data["password"])) {
                $error['password'] = App::t('Password is required.');
            }

            if (!empty($error)) {
                return $this->render('login', [
                    'error' => $error,
                    'data' => [
                        'email' => $data['email'],
                    ],
                ]);
            }

            $id = User::findUserByCredentials($data['email'], $data['password']);

            if (!empty($id)) {
                App::$session->create($id, $data["email"]);
                return $this->redirect('site/index');
            }

            $error['password'] = App::t('Email or password is incorrect.');

            return $this->render('login', [
                'error' => $error,
                'data' => [
                    'email' => $data['email'],
                ],
            ]);
        }

        App::$session->generateCSRF(true);

        return $this->render('login', [
            'error' => $error,
            'data' => [],
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionRegister(): string
    {
        session_start();

        if (App::$session->isLoggedIn()) {
            return $this->redirect('site/index');
        }

        $error = [];

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            if (empty($data["email"])) {
                $error['email'] = App::t('Email is required.');
            }

            if (empty($data["password"])) {
                $error['password'] = App::t('Password is required.');
            }

            if (empty($data["re_password"])) {
                $error['re_password'] = App::t('Retype password is required.');
            }

            if ($data["password"] !== $data["re_password"]) {
                $error['re_password'] = App::t('Passwords not match.');
            }

            if (!empty($error)) {
                return $this->render('register', [
                    'error' => $error,
                    'data' => [
                        'email' => $data['email'],
                    ],
                ]);
            }

            $id = User::register($data['email'], $data['password']);

            if (!empty($id)) {
                App::$session->create($id, $data["email"]);

                return $this->redirect('site/index');
            } else {
                $error['password'] = App::t('Email or password is incorrect.');
            }

            return $this->render('register', [
                'error' => $error,
                'data' => [
                    'email' => $data['email'],
                ],
            ]);
        }

        return $this->render('register', ['error' => $error, 'data' => []]);
    }

    #[NoReturn] public function actionLogout(): string
    {
        session_start();
        App::$session->destroy();
        return $this->redirect('auth/login');
    }

}