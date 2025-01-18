<?php

namespace app\core;

class Session
{

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
    }

    public static function isGuest(): bool
    {
        return !self::isLoggedIn();
    }

    public static function create(int $id, string $email): void
    {
        $_SESSION['_id'] = $id;
        $_SESSION['_email'] = $email;
        $_SESSION['loggedIn'] = true;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public static function _id()
    {
        return $_SESSION['_id'];
    }

    public static function notify(string $type, string $message): void
    {
        $_SESSION['alerts'][] = [
            'type' => $type,
            'msg' => $message,
        ];
    }

    public static function alerts(): array
    {
        $alerts = $_SESSION['alerts'] ?? [];

        unset($_SESSION['alerts']);

        return $alerts;
    }

}