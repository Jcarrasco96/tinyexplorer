<?php

namespace TE\helpers;

use TE\core\App;
use TE\core\BaseController;
use TE\services\FileSystem;

class Utils
{

    public static function cleanPath(string $p = ''): array
    {
        if (!empty($p)) {
            App::$session->setPath($p);
            $p = base64_decode($p);
        } else {
            App::$session->setPath('');
        }

        $path = App::$system->rootPath;
        if ($p != '') {
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            BaseController::redirect('site/index');
        }

        return [$p, $path];
    }

    public static function urlTo(string $path): string
    {
        $isHttps = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || ($_SERVER['x-forwarded-proto'] ?? '') === 'https';

        $port = $_SERVER['SERVER_PORT'];

        $baseUrl = ($isHttps ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ($port == 80 ? '' : ":$port") . '/';

        return $baseUrl . (App::$config['folder_name'] ? App::$config['folder_name'] . '/' : '') . $path;
    }

    public static function enc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function download($fileLocation, $fileName, $maxSpeed = 100, $doStream = false): bool
    {
        if (connection_status() != 0) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $contentType = FileSystem::fileTypes($fileLocation);
        $contentDisposition = 'attachment';

        if ($doStream && in_array($extension, ['pdf', 'mp3', 'm3u', 'm4a', 'mid', 'ogg', 'ra', 'ram', 'wm', 'wav', 'wma', 'aac', '3gp', 'avi', 'mov', 'mp4', 'mpeg', 'mpg', 'swf', 'wmv', 'divx', 'asf'])) {
            $contentDisposition = 'inline';
        }

        if (isset($_SERVER['HTTP_USER_AGENT']) && str_contains($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
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
            die(App::t('Zero byte file! Aborting download.'));
        }

        if (isset($_SERVER['HTTP_RANGE'])) {
            list(, $range) = explode("=", $_SERVER['HTTP_RANGE']);
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
            sleep(1);
        }
        fclose($fp);

        return connection_status() == 0 and !connection_aborted();
    }

}