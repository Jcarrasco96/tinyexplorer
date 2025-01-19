<?php

namespace app\services;

class FileSystem
{

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

    public static function parentPath(string $path): bool|string
    {
        $path = self::cleanPath($path);
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

    public static function cleanPath(string $path, bool $trim = true): string
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

    public static function filesize(int $size): string
    {
        $size = (float)$size;
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = ($size > 0) ? floor(log($size, 1024)) : 0;
        $power = ($power > (count($units) - 1)) ? (count($units) - 1) : $power;
        return sprintf('%s %s', round($size / pow(1024, $power), 2), $units[$power]);
    }

    public static function fileIconClass(string $path): string
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

    public static function rename(string $old, string $new): bool
    {
        return !file_exists($new) && file_exists($old) && rename($old, $new);
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

    public static function realDelete(string $path): bool
    {
        if (is_link($path)) {
            return unlink($path);
        }
        if (is_dir($path)) {
            $objects = scandir($path);
            $ok = true;
            if (is_array($objects)) {
                foreach ($objects as $file) {
                    if ($file != '.' && $file != '..') {
                        if (!self::realDelete($path . '/' . $file)) {
                            $ok = false;
                        }
                    }
                }
            }
            return $ok && rmdir($path);
        }
        if (is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    public static function convertWin(string $filename): string
    {
        if (DIRECTORY_SEPARATOR == '\\' && function_exists('iconv')) {
            $filename = iconv('UTF-8', 'UTF-8//IGNORE', $filename);
        }
        return $filename;
    }

    public static function typeFile(string $targetPath): string
    {
        $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        return match ($ext) {
            'ico', 'gif', 'jpg', 'jpeg', 'jpc', 'jp2', 'jpx', 'xbm', 'wbmp', 'png', 'bmp', 'tif', 'tiff', 'psd', 'svg', 'webp', 'avif' => 'image',
            'avi', 'webm', 'wmv', 'mp4', 'm4v', 'ogm', 'ogv', 'mov', 'mkv' => 'video',
            'wav', 'mp3', 'ogg', 'm4a' => 'audio',
            'txt', 'css', 'ini', 'conf', 'log', 'htaccess', 'passwd', 'ftpquota', 'sql', 'js', 'ts', 'jsx', 'tsx', 'mjs', 'json', 'sh', 'config', 'php', 'php4', 'php5', 'phps', 'phtml', 'htm', 'html', 'shtml', 'xhtml', 'xml', 'xsl', 'm3u', 'm3u8', 'pls', 'cue', 'bash', 'vue', 'eml', 'msg', 'csv', 'bat', 'twig', 'tpl', 'md', 'gitignore', 'less', 'sass', 'scss', 'c', 'cpp', 'cs', 'py', 'go', 'zsh', 'swift', 'map', 'lock', 'dtd', 'asp', 'aspx', 'asx', 'asmx', 'ashx', 'jsp', 'jspx', 'cgi', 'dockerfile', 'ruby', 'yml', 'yaml', 'toml', 'vhost', 'scpt', 'applescript', 'csx', 'cshtml', 'c++', 'coffee', 'cfm', 'rb', 'graphql', 'mustache', 'jinja', 'http', 'handlebars', 'java', 'es', 'es6', 'markdown', 'wiki', 'tmp', 'top', 'bot', 'dat', 'bak', 'htpasswd', 'pl' => 'text',
            'pdf', 'zip' => 'application',
            default => 'unknown',
        };
    }

    public static function extensionByType(string $type): array
    {
        return match ($type) {
            'image' => ['ico', 'gif', 'jpg', 'jpeg', 'jpc', 'jp2', 'jpx', 'xbm', 'wbmp', 'png', 'bmp', 'tif', 'tiff', 'psd', 'svg', 'webp', 'avif'],
            'video' => ['avi', 'webm', 'wmv', 'mp4', 'm4v', 'ogm', 'ogv', 'mov', 'mkv'],
            'audio' => ['wav', 'mp3', 'ogg', 'm4a'],
            'text' => ['txt', 'css', 'ini', 'conf', 'log', 'htaccess', 'passwd', 'ftpquota', 'sql', 'js', 'ts', 'jsx', 'tsx', 'mjs', 'json', 'sh', 'config', 'php', 'php4', 'php5', 'phps', 'phtml', 'htm', 'html', 'shtml', 'xhtml', 'xml', 'xsl', 'm3u', 'm3u8', 'pls', 'cue', 'bash', 'vue', 'eml', 'msg', 'csv', 'bat', 'twig', 'tpl', 'md', 'gitignore', 'less', 'sass', 'scss', 'c', 'cpp', 'cs', 'py', 'go', 'zsh', 'swift', 'map', 'lock', 'dtd', 'asp', 'aspx', 'asx', 'asmx', 'ashx', 'jsp', 'jspx', 'cgi', 'dockerfile', 'ruby', 'yml', 'yaml', 'toml', 'vhost', 'scpt', 'applescript', 'csx', 'cshtml', 'c++', 'coffee', 'cfm', 'rb', 'graphql', 'mustache', 'jinja', 'http', 'handlebars', 'java', 'es', 'es6', 'markdown', 'wiki', 'tmp', 'top', 'bot', 'dat', 'bak', 'htpasswd', 'pl'],
            'application' => ['pdf', 'zip'],
            default => [],
        };
    }
}