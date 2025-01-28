<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\http\JsonResponse;
use app\models\User;
use Exception;

class AdminController extends BaseController
{

    /**
     * @throws Exception
     */
    public function actionUsers(): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        $users = User::users();

        return $this->render('users', [
            'users' => $users,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionNewUser(): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax() || App::$session->isGuest()) {
            return $this->asJson($jsonResponse->json(400));
        }

        if ($this->isPost()) {
            $this->validateCsrf('site/login');

            $data = $this->getPostData();

            if (empty($data["username"])) {
                $jsonResponse->addError('username', App::t('Username is required.'));
            }

            if (empty($data["password"])) {
                $jsonResponse->addError('password', App::t('Password is required.'));
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            $id = User::new($data["username"], $data["password"]);

            if ($id) {
                $jsonResponse->set('success', App::t('User successfully registered.'));
            } else {
                $jsonResponse->set('error', App::t('User registration failed.'));
            }

            return $this->asJson($jsonResponse->json(400));
        }

        App::$session->generateCSRF(true);

        return $this->renderPartial('_new-user', [

        ]);
    }

    /**
     * @throws Exception
     */
    public function actionDelete(int $id): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        if (App::$session->_id() == $id) {
            App::$session->notify('g-warning', 'Not allowed delete your own user.');
            $this->redirect('site/index');
        }

        if ($this->isPost() && $this->isAjax()) {
            $this->validateCsrf('site/login');

            $jsonResponse = new JsonResponse('success', App::t('User deleted successfully.'));

            App::$session->notify('g-success', 'User deleted successfully.');

            User::delete($id);

            return $this->asJson($jsonResponse->json());
        }

        App::$session->generateCSRF(true);

        return $this->renderPartial('_delete', [
            'id' => $id,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionChangeAttribute(int $id, string $attribute): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        if (App::$session->_id() == $id) {
            App::$session->notify('g-warning', 'Not allowed delete your own user.');
            $this->redirect('site/index');
        }

        if (User::update($id, $attribute)) {
            return $this->asJson(['status' => 'success', 'message' => App::t('Attribute changed successfully.')]);
        }

        return $this->asJson(['status' => 'error', 'message' => App::t('Attribute not changed.')]);
    }

    /**
     * @throws Exception
     */
    public function actionChangePassword(int $id): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax() || App::$session->isGuest()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $isMe = App::$session->_id() == $id;

        if ($this->isPost()) {
            $this->validateCsrf('site/login');

            $data = $this->getPostData();

            if ($isMe && empty($data["old_password"])) {
                $jsonResponse->addError('old_password', App::t('Old password is required.'));
            }

            if (empty($data["new_password"])) {
                $jsonResponse->addError('new_password', App::t('New password is required.'));
            }

            if (empty($data["re_password"])) {
                $jsonResponse->addError('re_password', App::t('Retype password is required.'));
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            if ($data["new_password"] != $data["re_password"]) {
                $jsonResponse->addError('re_password', App::t('Retype password not matched.'));
            }

            if ($isMe) {
                $success = User::changeMyPassword($id, $data["new_password"], $data["old_password"]);
            } else {
                $success = User::changePassword($id, $data["new_password"]);
            }

            if ($success) {
                $jsonResponse->set('success', App::t('Password successfully changed.'));
            } else {
                $jsonResponse->set('error', App::t('Password not changed.'));
            }

            return $this->asJson($jsonResponse->json(400));
        }

        App::$session->generateCSRF(true);

        return $this->renderPartial('_change_password', [
            'isMe' => $isMe,
            'id' => $id,
        ]);
    }

}