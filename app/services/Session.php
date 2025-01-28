<?php

namespace app\services;

use app\models\User;
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

    public function create(int $id, string $email, array $permissions = []): void
    {
        $_SESSION['_id'] = $id;
        $_SESSION['_email'] = $email;
        $_SESSION['loggedIn'] = true;

        $_SESSION['permissions'] = $permissions;
        $_SESSION['last_permission_check'] = time();
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

    public function _permissions()
    {
        return $_SESSION['permissions'] ?? [];
    }

    public function setPermission(string $key, bool $value): void
    {
        if (in_array($key, ['cDelete', 'cUpload', 'cRename', 'cCopy', 'cCompress', 'cDownload', 'cShare', 'cAdmin'])) {
            $_SESSION['permissions'][$key] = $value;
        }
    }

    public function getPermission(string $key): bool
    {
        return $_SESSION['permissions'][$key] ?? false;
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

    public function path(bool $decoded = false): string
    {
        if ($decoded) {
            return base64_decode($_SESSION['_path'] ?? '');
        }
        return $_SESSION['_path'] ?? '';
    }

    public function setPath(string $p): void
    {
        $_SESSION['_path'] = $p;
    }

    function checkAndUpdatePermissions(): void
    {
        $interval = 300;
        $time = time();

        if (!$this->isGuest() && (!isset($_SESSION['last_permission_check']) || ($time - $_SESSION['last_permission_check']) > $interval)) {
            $newPermissions = self::getPermissionsFromDatabase($this->_id());

            $_SESSION['permissions'] = $newPermissions;
            $_SESSION['last_permission_check'] = $time;
        }
    }

    function getPermissionsFromDatabase(int $userId): array
    {
        $user = User::findById($userId);

        return $user['info'];
    }

}