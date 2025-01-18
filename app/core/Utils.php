<?php

namespace app\core;

use JetBrains\PhpStorm\NoReturn;

class Utils
{

    public static function urlTo(string $path): string
    {
        if (App::$config['folder_name'] === '') {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . $path;
        }
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/' . App::$config['folder_name'] . '/' . $path;
    }

    #[NoReturn] public static function fmRedirect(string $url, int $code = 302): void
    {
        header("Location: " . self::urlTo($url), true, $code);
        exit();
    }

    public static function fmCleanPath(string $path, bool $trim = true): string
    {
        $path = $trim ? trim($path) : $path;
        $path = trim($path, '\\/');
        $path = str_replace(['../', '..\\'], '', $path);
        $path = self::getAbsolutePath($path);
        if ($path == '..') {
            $path = '';
        }
        return str_replace('\\', '/', $path);
    }

    public static function getAbsolutePath(array|string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function fmGetParentPath(string $path): bool|string
    {
        $path = self::fmCleanPath($path);
        if ($path != '') {
            $array = explode('/', $path);
            if (count($array) > 1) {
                $array = array_slice($array, 0, -1);
                return implode('/', $array);
            }
            return '';
        }
        return false;
    }

    public static function fmEnc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function fmConvertWin(string $filename): string
    {
        if (DIRECTORY_SEPARATOR == '\\' && function_exists('iconv')) {
            $filename = iconv('UTF-8', 'UTF-8//IGNORE', $filename);
        }
        return $filename;
    }

    public static function fmGetFileIconClass(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'ico', 'gif', 'jpg', 'jpeg', 'jpc', 'jp2', 'jpx', 'xbm', 'wbmp', 'png', 'bmp', 'tif', 'tiff', 'webp', 'avif', 'svg' => 'bi bi-file-image',
            'passwd', 'ftpquota', 'sql', 'js', 'ts', 'jsx', 'tsx', 'hbs', 'json', 'sh', 'config', 'twig', 'tpl', 'md', 'gitignore', 'c', 'cpp', 'cs', 'py', 'rs', 'map', 'lock', 'dtd', 'php', 'php4', 'php5', 'phps', 'phtml', 'vb' => 'bi bi-file-code',
            'txt', 'ini', 'conf', 'log', 'htaccess', 'yaml', 'yml', 'toml', 'tmp', 'top', 'bot', 'dat', 'bak', 'htpasswd', 'pl', 'csv' => 'bi bi-file-text',
            'css', 'less', 'sass', 'scss' => 'bi bi-filetype-css',
            'bz2', 'zip', 'rar', 'gz', 'tar', '7z', 'xz', 'tgz' => 'bi bi-file-zip',
            'htm', 'html', 'shtml', 'xhtml' => 'bi bi-filetype-html',
            'xsl', 'xls', 'xlsx', 'ods' => 'bi bi-file-excel',
            'wav', 'mp3', 'mp2', 'm4a', 'aac', 'ogg', 'oga', 'wma', 'mka', 'flac', 'ac3', 'tds' => 'bi bi-file-music',
            'm3u', 'm3u8', 'pls', 'cue', 'xspf' => 'bi bi-file-play',
            'avi', 'mpg', 'mpeg', 'mp4', 'm4v', 'flv', 'f4v', 'ogm', 'ogv', 'mov', 'mkv', '3gp', 'asf', 'wmv', 'webm' => 'bi bi-file-slides',
            'eml', 'msg' => 'bi bi-file-post',
            'doc', 'docx', 'odt' => 'bi bi-file-word',
            'ppt', 'pptx' => 'bi bi-file-ppt',
            'ttf', 'ttc', 'otf', 'woff', 'woff2', 'eot', 'fon' => 'bi bi-file-font',
            'pdf' => 'bi bi-file-pdf',
            'psd', 'ai', 'eps', 'fla', 'swf' => 'bi bi-file-easel',
            'exe', 'msi' => 'bi bi-filetype-exe',
            'bat' => 'bi bi-terminal',
            'iso' => 'bi bi-disc',
            'xml' => 'bi bi-file-xml',
            default => 'bi bi-file-binary',
        };
    }

    public static function fmGetFilesize(int $size): string
    {
        $size = (float)$size;
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = ($size > 0) ? floor(log($size, 1024)) : 0;
        $power = ($power > (count($units) - 1)) ? (count($units) - 1) : $power;
        return sprintf('%s %s', round($size / pow(1024, $power), 2), $units[$power]);
    }

    public static function isValidFilename(string $filename): bool|string
    {
        return strpbrk($filename, '/?%*:|"<>') === FALSE;
    }

    public static function mkdir($dir, $force)
    {
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                return $dir;
            } elseif (!$force) {
                return false;
            }
            unlink($dir);
        }
        return mkdir($dir, 0777, true);
    }

    public static function rename(string $old, string $new): bool
    {
        return !file_exists($new) && file_exists($old) && rename($old, $new);
    }

    public static function fileTypes(string $extension): string
    {
        $fileTypes = [
            'swf' => 'application/x-shockwave-flash',
            'pdf' => 'application/pdf',
            'exe' => 'application/octet-stream',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpeg' => 'image/jpg',
            'jpg' => 'image/jpg',
            'rar' => 'application/rar',

            'ra' => 'audio/x-pn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'ogg' => 'audio/x-pn-realaudio',

            'wav' => 'video/x-msvideo',
            'wmv' => 'video/x-msvideo',
            'avi' => 'video/x-msvideo',
            'asf' => 'video/x-msvideo',
            'divx' => 'video/x-msvideo',

            'mp3' => 'audio/mpeg',
            'mp4' => 'audio/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mov' => 'video/quicktime',
            '3gp' => 'video/quicktime',
            'm4a' => 'video/quicktime',
            'aac' => 'video/quicktime',
            'm3u' => 'video/quicktime',
        ];

        return $fileTypes[$extension] ?? 'application/octet-stream';
    }

    public static function download($fileLocation, $fileName, $maxSpeed = 100, $doStream = false)
    {
        if (connection_status() != 0) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $contentType = self::fileTypes($extension);
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
            sleep(1);
        }
        fclose($fp);

        return connection_status() == 0 and !connection_aborted();
    }

}