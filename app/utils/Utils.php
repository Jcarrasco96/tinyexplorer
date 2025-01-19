<?php

namespace app\utils;

use app\core\App;
use app\services\FileSystem;

class Utils
{

    public static function base64UrlEncode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    public static function byteLength(string $string): int
    {
        return mb_strlen($string, '8bit');
    }

    public static function urlTo(string $path): string
    {
        if (App::$config['folder_name'] === '') {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . $path;
        }
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/' . $path;
    }

    public static function enc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function download($fileLocation, $fileName, $maxSpeed = 100, $doStream = false)
    {
        if (connection_status() != 0) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $contentType = FileSystem::fileTypes($extension);
        $contentDisposition = 'attachment';

        if ($doStream) {
            /* extensions to stream */
            $array_listen = ['mp3', 'm3u', 'm4a', 'mid', 'ogg', 'ra', 'ram', 'wm', 'wav', 'wma', 'aac', '3gp', 'avi', 'mov', 'mp4', 'mpeg', 'mpg', 'swf', 'wmv', 'divx', 'asf'];
            if (in_array($extension, $array_listen)) {
                $contentDisposition = 'inline';
            }
        }

        if (str_contains($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
        }

        header("Cache-Control: public");
        header("Content-Transfer-Encoding: binary\n");
        header("Content-Type: $contentType");
        header("Content-Disposition: $contentDisposition; filename=\"$fileName\"");
        header("Accept-Ranges: bytes");

        $range = 0;
        $size = filesize($fileLocation);

        if ($size == 0) {
            die('Zero byte file! Aborting download');
        }

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            str_replace($range, "-", $range);
            $size2 = $size - 1;
            $new_length = $size - $range;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range$size2/$size");
        } else {
            $size2 = $size - 1;
            header("Content-Range: bytes 0-$size2/$size");
            header("Content-Length: " . $size);
        }

        $fp = fopen("$fileLocation", "rb");

        fseek($fp, $range);

        while (!feof($fp) and connection_status() == 0) {
            set_time_limit(0);
            print(fread($fp, 1024 * $maxSpeed));
            flush();
            ob_flush();
            sleep(0.5);
        }
        fclose($fp);

        return connection_status() == 0 and !connection_aborted();
    }

}