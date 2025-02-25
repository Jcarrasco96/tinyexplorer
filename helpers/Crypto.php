<?php

namespace TE\helpers;

use Random\RandomException;

class Crypto
{

    private static string $method = 'aes-256-cbc';
    private static string $key;

    public static function init(string $key): void
    {
        self::$key = $key;
    }

    /**
     * @throws RandomException
     */
    public static function encrypt(string $data): array
    {
        $iv = bin2hex(random_bytes(8));

        return [
            'data' => base64_encode(openssl_encrypt($data, self::$method, self::$key, 0, $iv)),
            'iv' => base64_encode($iv),
        ];
    }

    public static function decrypt(string $data, string $iv): string
    {
        return openssl_decrypt(base64_decode($data), self::$method, self::$key, 0, base64_decode($iv));
    }

}