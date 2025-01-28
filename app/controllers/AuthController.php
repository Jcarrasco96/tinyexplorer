<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\models\User;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class AuthController extends BaseController
{

    public string $layout = 'guest';

    /**
     * @throws Exception
     */
    public function actionLogin(): string
    {
        if (App::$session->isLoggedIn()) {
            $this->redirect('site/index');
        }

        $error = [];

        if ($this->isPost()) {
            $data = $this->getPostData();

            $this->validateCsrf('site/login');

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

            $user = User::findUserByCredentials($data['email'], $data['password']);

            if (!empty($user)) {
                App::$session->create($user['id'], $user["username"], json_decode($user["info"], true));

                $redirectUrl = $_GET['redirect'] ?? 'site/index';

                if (str_contains($redirectUrl, '//')) {
                    $redirectUrl = 'site/index';
                }

                $this->redirect($redirectUrl);
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
        if (App::$session->isLoggedIn()) {
            $this->redirect('site/index');
        }

        $error = [];

        if ($this->isPost()) {
            $data = $this->getPostData();

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

                $this->redirect('site/index');
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
        App::$session->destroy();
        $this->redirect('auth/login');
    }

}