<?php

namespace app\services;

use app\utils\Utils;
use Exception;
use Random\RandomException;

class Security
{

    /**
     * @throws RandomException
     * @throws Exception
     */
    public static function generateRandomString(int $length = 32): string
    {
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }

        return substr(Utils::base64UrlEncode(self::generateRandomKey($length)), 0, $length);
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public static function generateRandomKey(int $length = 32): string
    {
        if ($length < 1) {
            throw new Exception('First parameter ($length) must be greater than 0');
        }

        return random_bytes($length);
    }

    /**
     * @throws Exception
     */
    public static function generatePasswordHash(string $password, int $cost = 13): string
    {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
        }

        $salt = self::generateSalt($cost);
        $hash = crypt($password, $salt);
        // strlen() is safe since crypt() returns only ascii
        if (strlen($hash) !== 60) {
            throw new Exception('Unknown error occurred while generating hash.');
        }

        return $hash;
    }

    /**
     * @throws Exception
     */
    public static function validatePassword(int $password, string $hash): bool
    {
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches) || $matches[1] < 4 || $matches[1] > 30) {
            throw new Exception('Hash is invalid.');
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }

        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }

        return self::compareString($test, $hash);
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    protected static function generateSalt(int $cost = 13): string
    {
        if ($cost < 4 || $cost > 31) {
            throw new Exception('Cost must be between 4 and 31.');
        }

        // Get a 20-byte random string
        $rand = self::generateRandomKey(20);
        // Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
        $salt = sprintf('$2y$%02d$', $cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));

        return $salt;
    }

    public static function compareString(string $expected, string $actual): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($expected, $actual);
        }

        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = Utils::byteLength($expected);
        $actualLength = Utils::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }

        return $diff === 0;
    }

}