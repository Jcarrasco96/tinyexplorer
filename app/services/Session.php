<?php

namespace app\services;

use Exception;
use Random\RandomException;

class Session
{

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
    }

    public function isGuest(): bool
    {
        return !$this->isLoggedIn();
    }

    public function create(int $id, string $email): void
    {
        $_SESSION['_id'] = $id;
        $_SESSION['_email'] = $email;
        $_SESSION['loggedIn'] = true;
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public function _id()
    {
        return $_SESSION['_id'];
    }

    public function notify(string $type, string $message): void
    {
        $_SESSION['alerts'][] = [
            'type' => $type,
            'msg' => $message,
        ];
    }

    public function alerts(): array
    {
        $alerts = $_SESSION['alerts'] ?? [];

        unset($_SESSION['alerts']);

        return $alerts;
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public function generateCSRF(bool $force = false): void
    {
        if (empty($_SESSION['_csrf_token']) || $force) {
            $_SESSION['_csrf_token'] = Security::generateRandomString();
        }
    }

    public function _csrf(): string
    {
        return $_SESSION['_csrf_token'] ?? '';
    }

    public function checkCSRF(string $_csrf): bool
    {
        if (empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $_csrf);
    }

}