<?php

namespace TE\services;

use Firebase\JWT\JWT;
use TE\core\App;
use TE\helpers\Utils;

class FileSystem
{

    public static function getArrFile(string $new_path, string $f): array
    {
        $fs = filesize($new_path);

        $p = App::$session->path(true);

        $payload = [
//            'sub' => '1234567890',
            'iss' => 'jcarrasco96.com',
            'aud' => 'nas.jcarrasco96.org',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'p' => $p,
                'f' => $f,
            ],
        ];

        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : self::fileIconClass($new_path),
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
//            'filesize_raw' => $fs,
            'filesize' => self::filesize($fs),
//            'link' => App::$session->path() . '&amp;view=' . base64_encode($f),
            'f' => $f,
//            'isFile' => true,
            'jwt' => JWT::encode($payload, App::$config['jwtSecretKey'], 'HS256'),
//            'raw' => Utils::urlTo('api/raw?file=' . base64_encode($new_path)),
        ];
    }

    public static function getArrFolder(string $new_path, string $f): array
    {
        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : 'bi bi-folder',
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'link' => base64_encode(trim(App::$session->path(true) . '/' . $f, '/')),
//            'link2' => base64_encode(trim($path . '/' . $f, '/')),
            'f' => $f,
//            'isFile' => false,
        ];
    }

    public static function filesAndFoldersInPath(string $path, string $search = null): array
    {
        $objects = is_readable($path) ? scandir($path) : [];

        $files = [];
        $folders = [];

        if (is_array($objects)) {
            foreach ($objects as $file) {
                if ($file == '.' || $file == '..' || str_starts_with($file, '.') || $file == 'System Volume Information' || $file == '$RECYCLE.BIN') {
                    continue;
                }
                if ($file == 'desktop.ini') {
                    continue;
                }

                if ($search && !preg_match("/(" . preg_quote($search, '/') . ")/i", $file)) {
                    continue;
                }

                $new_path = $path . '/' . $file;

                if (@is_file($new_path)) {
                    $files[] = self::getArrFile($new_path, $file);
                } elseif (@is_dir($new_path)) {
                    $folders[] = self::getArrFolder($new_path, $file);
                }
            }
        }

        if (!empty($files)) {
            usort($files, fn($a, $b) => strcasecmp($a['f'], $b['f']));
        }
        if (!empty($folders)) {
            usort($folders, fn($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        return [$files, $folders];
    }

    public static function foldersInPath(string $path): array
    {
        $objects = is_readable($path) ? scandir($path) : [];
        $folders = [];

        if (is_array($objects)) {
            foreach ($objects as $vFile) {
                if ($vFile == '.' || $vFile == '..' || str_starts_with($vFile, '.') || $vFile == 'System Volume Information' || $vFile == '$RECYCLE.BIN') {
                    continue;
                }

                $new_path = $path . '/' . $vFile;

                if (!is_dir($new_path)) {
                    continue;
                }

                $folders[] = self::getArrFolder($new_path, $vFile);
            }
        }

        if (!empty($folders)) {
            usort($folders, fn($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        return $folders;
    }


    public static function diskUsage($path = '/'): array
    {
        $total = disk_total_space($path);
        $free = disk_free_space($path);

        $used = $total - $free;

        return [
            'total' => self::filesize($total),
            'free' => self::filesize($free),
            'used' => self::filesize($used),
            'percent' => intval($used / $total * 100),
        ];
    }

    public static function isNAS($path): bool
    {
        $path = rtrim($path, '\\/');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (str_starts_with($path, '\\\\')) {
                return true;
            }

            $networkDevices = shell_exec('net use');
            $lines = explode("\n", $networkDevices);

            $path = substr($path, 0, 2);

            foreach ($lines as $linea) {
                if (str_contains($linea, $path) && str_contains($linea, 'Network')) {
                    return true;
                }
            }
        } else {
            $mounts = file('/proc/mounts');

            foreach ($mounts as $mount) {
                $parts = explode(' ', $mount);
                if (isset($parts[1]) && isset($parts[2])) {
                    $mountPoint = rtrim($parts[1], '/');
                    $fileSystem = $parts[2];

                    if ($mountPoint === $path && in_array($fileSystem, ['cifs', 'nfs', 'smb'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function isValidFilename(string $filename): bool|string
    {
        return strpbrk($filename, '/?%*:|"<>') === FALSE;
    }

    public static function mkdir($dir, $force)
    {
        if (file_exists($dir) && (is_dir($dir) || (!$force && !unlink($dir)))) {
            return is_dir($dir) ? $dir : false;
        }

        return mkdir($dir, 0777, true);
    }

    public static function parentPath(string $path): bool|string
    {
        return $path ? implode('/', array_slice(explode('/', $path), 0, -1)) : false;
    }

    public static function cleanPath(string $path, bool $trim = true): string
    {
        $path = $trim ? trim($path) : $path;
        $path = str_replace(['../', '..\\'], ['', ''], trim($path, '\\/'));
        $path = self::getAbsolutePath($path);

        return $path === '..' ? '' : str_replace('\\', '/', $path);
    }

    public static function fileTypes(string $path): string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($path) ?: 'application/octet-stream';
        }

        if (function_exists('finfo_open')) {
            $fInfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($fInfo, $path);
            finfo_close($fInfo);
            return $mimetype ?: 'application/octet-stream';
        }

        $mimetypes = require_once 'mimetypes.php';
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return $mimetypes[$extension] ?? 'application/octet-stream';
    }

    public static function filesize(float $size): string
    {
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

    public static function rename(string $old, string $new): ?bool
    {
        return !file_exists($new) && file_exists($old) && rename($old, $new);
    }

    public static function recursiveCopy(string $path, string $dest, bool $override = true, bool $force = true): bool
    {
        if (is_dir($path)) {
            if (!self::mkdir($dest, $force)) {
                return false;
            }
            $objects = scandir($path);
            $ok = true;
            if (is_array($objects)) {
                foreach ($objects as $file) {
                    if ($file != '.' && $file != '..') {
                        if (!self::recursiveCopy($path . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file)) {
                            $ok = false;
                        }
                    }
                }
            }
            return $ok;
        } elseif (is_file($path)) {
            return self::copy($path, $dest, $override);
        }
        return false;
    }

    public static function copy(string $path, string $dest, bool $override = true): bool
    {
        $time1 = filemtime($path);
        if (file_exists($dest) && filemtime($dest) >= $time1 && !$override) {
            return false;
        }
        return copy($path, $dest) && touch($dest, $time1);
    }

    public static function getAbsolutePath(array|string $path): string
    {
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path)), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            $part === '..' ? array_pop($absolutes) : ($part !== '.' && $absolutes[] = $part);
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function realDelete(string $path): bool
    {
        if (is_link($path) || is_file($path)) {
            return unlink($path);
        }
        if (!is_dir($path)) {
            return false;
        }

        $objects = scandir($path);
        $ok = true;
        foreach ($objects as $file) {
            if (in_array($file, ['.', '..', 'System Volume Information', '$RECYCLE.BIN'])) continue;
            $ok = $ok && self::realDelete($path . DIRECTORY_SEPARATOR . $file);
        }
        return $ok && rmdir($path);
    }

    public static function convertWin(string $filename): string
    {
        return (DIRECTORY_SEPARATOR === '\\' && function_exists('iconv')) ? iconv('UTF-8', 'UTF-8//IGNORE', $filename) : $filename;
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