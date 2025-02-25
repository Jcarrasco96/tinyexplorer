<?php

namespace TE\controllers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JetBrains\PhpStorm\NoReturn;
use TE\core\App;
use TE\core\BaseController;
use TE\core\ControllerPermission;
use TE\helpers\Utils;
use TE\models\User;
use TE\services\FileSystem;

class AuthController extends BaseController
{

    public string $layout = 'guest';

    /**
     * @throws Exception
     */
    #[ControllerPermission(['?'])]
    public function actionLogin(): string
    {
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
                return $this->render('login', ['error' => $error, 'data' => ['email' => $data['email']]]);
            }

            $user = User::findUserByCredentials($data['email'], $data['password']);

            if ($user) {
                App::$session->create($user['id'], $user["username"], $user["info"]);
                $redirectUrl = str_contains($_GET['redirect'] ?? 'site/index', '//') ? 'site/index' : $_GET['redirect'] ?? 'site/index';
                $this->redirect($redirectUrl);
            }

            $error['password'] = App::t('Email or password is incorrect.');
            return $this->render('login', ['error' => $error, 'data' => ['email' => $data['email']]]);
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
    #[ControllerPermission(['?', '*'])]
    public function actionRegister(): string
    {
//        if (App::$session->isLoggedIn()) {
//            $this->redirect('site/index');
//        }
//
        $error = [];
//
//        if ($this->isPost()) {
//            $data = $this->getPostData();
//
//            if (empty($data["email"])) {
//                $error['email'] = App::t('Email is required.');
//            }
//
//            if (empty($data["password"])) {
//                $error['password'] = App::t('Password is required.');
//            }
//
//            if (empty($data["re_password"])) {
//                $error['re_password'] = App::t('Retype password is required.');
//            }
//
//            if ($data["password"] !== $data["re_password"]) {
//                $error['re_password'] = App::t('Passwords not match.');
//            }
//
//            if (!empty($error)) {
//                return $this->render('register', [
//                    'error' => $error, 'data' => ['email' => $data['email']]]);
//            }
//
//            $id = User::register($data['email'], $data['password']);
//
//            if (!empty($id)) {
//                // todo findUserByCredentials
//                App::$session->create($id, $data["email"]);
//
//                $this->redirect('site/index');
//            } else {
//                $error['password'] = App::t('Email or password is incorrect.');
//            }
//
//            return $this->render('register', [
//                'error' => $error,
//                'data' => [
//                    'email' => $data['email'],
//                ],
//            ]);
//        }

        return $this->render('register', ['error' => $error, 'data' => []]);
    }

    #[ControllerPermission(['@'])]
    #[NoReturn] public function actionLogout(): string
    {
        App::$session->destroy();
        $this->redirect('auth/login');
    }

}