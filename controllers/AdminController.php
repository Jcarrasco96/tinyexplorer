<?php

namespace TE\controllers;

use Exception;
use TE\core\App;
use TE\core\BaseController;
use TE\core\ControllerPermission;
use TE\http\JsonResponse;
use TE\models\User;

class AdminController extends BaseController
{

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cAdmin'])]
    public function actionUsers(): string
    {
        $users = User::users();

        return $this->render('users', [
            'users' => $users,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cAdmin'])]
    public function actionNewUser(): string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
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

            $id = User::create($data);

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
    #[ControllerPermission(['cAdmin'])]
    public function actionDelete(int $id): string
    {
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
    #[ControllerPermission(['cAdmin'])]
    public function actionChangeAttribute(int $id, string $attribute): string
    {
        if (App::$session->_id() == $id) {
            App::$session->notify('g-warning', 'Not allowed delete your own user.');
            $this->redirect('site/index');
        }

        if (User::updateAttribute($id, $attribute)) {
            return $this->asJson(['status' => 'success', 'message' => App::t('Attribute changed successfully.')]);
        }

        return $this->asJson(['status' => 'error', 'message' => App::t('Attribute not changed.')]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cAdmin'])]
    public function actionChangePassword(int $id): string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
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
                $success = User::updateMyPassword($id, $data["new_password"], $data["old_password"]);
            } else {
                $success = User::updatePassword($id, $data["new_password"]);
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