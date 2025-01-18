<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\core\Session;
use app\core\Utils;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ZipArchive;

class SiteController extends BaseController
{

    /**
     * @throws Exception
     */
    public function actionIndex(): string
    {
        session_start();

        if (Session::isGuest()) {
            return $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';

        $path = App::$config['path'];
        if ($p != '') {
            $p = base64_decode($p);
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            return $this->redirect('site/index');
        }

        $parent = Utils::fmGetParentPath($p);

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

                $new_path = $path . '/' . $file;

                if (@is_file($new_path)) {
                    $files[] = $this->getArrFile($new_path, $p, $file);
                } elseif (@is_dir($new_path)) {
                    $folders[] = $this->getArrFolder($new_path, $p, $file);
                }
            }
        }

        if (!empty($files)) {
            usort($files, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }
        if (!empty($folders)) {
            usort($folders, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        return $this->render('index', [
            'p' => Utils::fmCleanPath($p),
            'arrFolders' => $folders,
            'arrFiles' => $files,
            'parent' => $parent,
        ]);
    }

    public function actionImage(): void
    {
        $path = base64_decode($_GET['image']);
        $type = $_GET['type'] ?? 'image';

        if (file_exists($path)) {
            header("Content-Type: $type/" . strtolower(pathinfo($path, PATHINFO_EXTENSION)));
            readfile($path);
            exit;
        } else {
            http_response_code(404);
            echo "File $path not found.";
        }
    }

    /**
     * @throws Exception
     */
    public function actionUpload(): string
    {
        session_start();

        if (Session::isGuest()) {
            return $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';

        if ($this->isPost()) {
            $response = ['status' => 'error', 'info' => 'Oops! Try again'];

            $fullPathInput = Utils::fmCleanPath($_POST['fullpath']);

            $tmp_name = $_FILES['file']['tmp_name'];
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) != '' ? strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) : '';

            $targetPath = App::$config['path'] . str_replace('/', DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR;

            if (is_writable($targetPath)) {
                $fullPath = App::$config['path'] . $p . '/' . basename($fullPathInput);

                if ($_POST['dztotalchunkcount']) {
                    $out = @fopen("$fullPath.part", $_POST['dzchunkindex'] == 0 ? "wb" : "ab");
                    if ($out) {
                        $in = @fopen($tmp_name, "rb");
                        if ($in) {
                            if (PHP_VERSION_ID < 80009) {
                                // workaround https://bugs.php.net/bug.php?id=81145
                                do {
                                    for (; ;) {
                                        $buff = fread($in, 4096);
                                        if ($buff === false || $buff === '') {
                                            break;
                                        }
                                        fwrite($out, $buff);
                                    }
                                } while (!feof($in));
                            } else {
                                stream_copy_to_stream($in, $out);
                            }
                        }
                        @fclose($in);
                        @fclose($out);
                        @unlink($tmp_name);

                        $response = ['status' => 'success', 'info' => "File upload successful"];
                    } else {
                        $response = ['status' => 'error', 'info' => "Failed to open output stream"];
                    }

                    if ($_POST['dzchunkindex'] == $_POST['dztotalchunkcount'] - 1) {
                        if (file_exists($fullPath)) {
                            $ext_1 = $ext ? '.' . $ext : '';
                            $fullPathTarget = App::$config['path'] . $p . '/' . basename($fullPathInput, $ext_1) . '_' . date('ymdHis') . $ext_1;
                        } else {
                            $fullPathTarget = $fullPath;
                        }
                        rename("$fullPath.part", $fullPathTarget);
                    }

                } else if (move_uploaded_file($tmp_name, $fullPath)) {
                    // Be sure that the file has been uploaded
                    if (file_exists($fullPath)) {
                        $response = ['status' => 'success', 'info' => "File upload successful"];
                    } else {
                        $response = ['status' => 'error', 'info' => 'Couldn\'t upload the requested file.'];
                    }
                } else {
                    $response = ['status' => 'error', 'info' => "Error while uploading files."];
                }
            }

            return $this->asJson($response);
        }

        return $this->render('upload', [
            'p' => Utils::fmCleanPath($p)
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionDownload(): void
    {
        $jwt = $_GET['jwt'] ?? '';

        $p = $_GET['p'] ?? '';
        $df = $_GET['df'] ?? '';

        if (!empty($jwt)) {
            try {
                $decoded = JWT::decode($jwt, new Key(App::$config['jwtSecretKey'], 'HS256'));

                $p = $decoded->data->p;
                $df = $decoded->data->f;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            session_start();

            if (Session::isGuest()) {
                throw new Exception('You are not allowed to access this page');
            }
        }

        $df = Utils::fmCleanPath($df);

        $targetPath = App::$config['path'] . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $df;

        if ($df != '' && is_file($targetPath)) {
            Utils::download($targetPath, $df, 1024 * 10, true);
            exit;
        } else {
            Session::notify('g-danger', 'File not found');
        }
    }

    /**
     * @throws Exception
     */
    public function actionNewAjax(): false|string
    {
        session_start();

        if (!$this->isAjax()) {
            return $this->asJson(['status' => 400, 'data' => [], 'error' => 'AJAX REQUIRED']);
        }

        if (Session::isGuest()) {
            return $this->asJson(['status' => 400, 'data' => [], 'error' => 'YOU MUST BE LOGGED IN']);
        }

        $p = $_GET['p'] ?? '';
        $t = $_GET['t'] ?? 'file';

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            $data['name'] = str_replace('/', '', Utils::fmCleanPath(strip_tags($data['name'])));

            $error = $this->getErrors($data);

            if (!empty($error)) {
                return $this->asJson(['status' => 400, 'error' => $error, 'message' => '']);
            }

            $targetPath = App::$config['path'] . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['name'];

            if ($t == 'file') {
                if (file_exists($targetPath)) {
                    return $this->asJson(['status' => 400, 'error' => ['name' => 'File already exists.'], 'message' => '']);
                }

                @fopen($targetPath, 'w') or die('Cannot open file:  ' . $data['name']);

                Session::notify('g-success', 'File created successfully');

                return $this->asJson(['status' => 200, 'error' => [], 'message' => 'File created successfully']);
            } else {
                if (Utils::mkdir($targetPath, false) === true) {
                    Session::notify('g-success', 'Folder created successfully');

                    return $this->asJson(['status' => 200, 'error' => [], 'message' => 'Folder created successfully.']);
                }

                if (Utils::mkdir($targetPath, false) === $targetPath) {
                    return $this->asJson(['status' => 400, 'error' => ['name' => 'Folder already exists.'], 'message' => '']);
                }

                return $this->asJson(['status' => 400, 'error' => ['name' => 'Folder not created.'], 'message' => '']);
            }
        }

        return $this->render('_new', [
            'action' => Utils::urlTo('site/new-ajax?p=' . $p . '&t=' . $t),
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionRename(): false|string
    {
        session_start();

        if (!$this->isAjax()) {
            return $this->asJson(['status' => 400, 'error' => 'AJAX REQUIRED']);
        }

        if (Session::isGuest()) {
            return $this->asJson(['status' => 400, 'error' => 'YOU MUST BE LOGGED IN']);
        }

        $p = $_GET['p'] ?? '';
        $old = $_GET['f'] ?? '';

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            $data['oldName'] = str_replace('/', '', Utils::fmCleanPath(strip_tags($data['oldName'])));
            $data['newName'] = str_replace('/', '', Utils::fmCleanPath(strip_tags($data['newName'])));

            $error = $this->getErrorsRename($data);

            if (!empty($error)) {
                return $this->asJson([
                    'status' => 400,
                    'error' => $error,
                    'message' => '',
                ]);
            }

            $targetOldPath = App::$config['path'] . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['oldName'];
            $targetNewPath = App::$config['path'] . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['newName'];

            if (Utils::rename($targetOldPath, $targetNewPath)) {
                Session::notify('g-success', 'File or folder renamed successfully');

                return $this->asJson(['status' => 200, 'error' => [], 'message' => '']);
            }

            return $this->asJson([
                'status' => 400,
                'error' => [],
                'message' => 'Error while renaming.',
            ]);
        }

        return $this->render('_rename', [
            'action' => Utils::urlTo('site/rename?p=' . $p),
            'oldName' => $old,
        ]);
    }

    protected function getErrors(array $data): array
    {
        $errors = [];

        if (empty($data["name"])) {
            $errors['name'] = "Name is required";
        }

        if (!Utils::isValidFilename($data["name"])) {
            $errors['name'] = "Invalid characters in file or folder name";
        }

        return $errors;
    }

    protected function getErrorsRename(array $data): array
    {
        $errors = [];

        if (empty($data["oldName"])) {
            $errors['oldName'] = "Name is required";
        }
        if (empty($data["newName"])) {
            $errors['newName'] = "Name is required";
        }

        if (!Utils::isValidFilename($data["oldName"])) {
            $errors['oldName'] = "Invalid characters in old file or folder name";
        }
        if (!Utils::isValidFilename($data["newName"])) {
            $errors['newName'] = "Invalid characters in new file or folder name";
        }

        return $errors;
    }

    /**
     * @throws Exception
     */
    public function actionView(): string
    {
        session_start();

        if (Session::isGuest()) {
            throw new Exception('You are not allowed to access this page');
        }

        $p = $_GET['p'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }

        $file = $_GET['view'] ?? '';

        if ($file != '') {
            $file = base64_decode($file);
        }

        $file = Utils::fmCleanPath($file);

        $path = App::$config['path'] . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);

        $targetPath = $path . DIRECTORY_SEPARATOR . $file;

        if ($file == '' || !is_file($targetPath)) {
            Session::notify('g-danger', 'File not found');
            $this->redirect('site/index');
        }

        $type = $this->typeFile($targetPath);

        $objects = is_readable($path) ? scandir($path) : [];

        $files = [];

        $extensionsByType = $this->extensionByType($type);

        if (is_array($objects)) {
            foreach ($objects as $f) {
                if ($f == '.' || $f == '..' || str_starts_with($f, '.') || $f == 'System Volume Information' || $f == '$RECYCLE.BIN') {
                    continue;
                }

                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));

                if (!in_array($ext, $extensionsByType) || $f == $file) {
                    continue;
                }

                $new_path = $path . '/' . $f;

                if (@is_file($new_path)) {
                    $files[] = $this->getArrFile($new_path, $p, $f);
                }
            }
        }

        if (!empty($files)) {
            usort($files, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        $extFile = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $filenames = [];

        if ($extFile == 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($targetPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zip_info = $zip->statIndex($i);

                    if (str_ends_with($zip_info['name'], '/')) {
                        continue;
                    }
                    $filenames[] = [
                        'name' => $zip_info['name'],
                        'filesize' => $zip_info['size'],
                        'compressed_size' => $zip_info['comp_size'],
//                        'isFolder' => str_ends_with($zip_info['name'], '/'),
                    ];
                }
                $zip->close();
            }
        }

        return $this->render('view', [
            'p' => $p,
            'file' => $file,
            'type' => $type,
            'url' => Utils::urlTo('site/image?image=' . base64_encode($targetPath)) . '&type=' . $type,
            'arrFiles' => $files,

            'filenames' => $filenames,
        ]);
    }

    private function typeFile(string $targetPath): string
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

    private function extensionByType(string $type): array
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

    public function getArrFile(string $new_path, mixed $p, mixed $f): array
    {
        $fs = filesize($new_path);

        $payload = [
            'iss' => 'jcarrasco96.com',
            'aud' => 'nas.jcarrasco96.org',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'p' => addslashes($p),
                'f' => addslashes($f),
            ],
        ];

        $jwt = JWT::encode($payload, App::$config['jwtSecretKey'], 'HS256');

        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : Utils::fmGetFileIconClass($new_path),
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'filesize_raw' => $fs,
            'filesize' => Utils::fmGetFilesize($fs),
            'link' => base64_encode($p) . '&amp;view=' . base64_encode($f),
            'f' => $f,
            'encFile' => Utils::fmEnc($f),
            'directLink' => $jwt,
        ];
    }

    public function getArrFolder(string $new_path, mixed $p, mixed $f): array
    {
        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : 'bi bi-folder',
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'link' => base64_encode(trim($p . '/' . $f, '/')),
            'f' => $f,
            'encFile' => Utils::fmEnc($f)
        ];
    }

}