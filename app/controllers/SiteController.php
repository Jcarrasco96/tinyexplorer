<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\http\JsonResponse;
use app\services\FileSystem;
use app\services\Zip;
use app\utils\Utils;
use Exception;
use Firebase\JWT\JWT;
use JetBrains\PhpStorm\NoReturn;
use ZipArchive;

class SiteController extends BaseController
{

    /**
     * @throws Exception
     */
    public function actionIndex(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            return $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';

        $path = App::$system->rootPath;
        if ($p != '') {
            $p = base64_decode($p);
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            return $this->redirect('site/index');
        }

        $parent = FileSystem::parentPath($p);

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
            'p' => FileSystem::cleanPath($p),
            'arrFolders' => $folders,
            'arrFiles' => $files,
            'parent' => $parent,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionUpload(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            return $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }

        return $this->render('upload', [
            'p' => FileSystem::cleanPath($p)
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionDownload(): void
    {
        session_start();

        if (App::$session->isGuest()) {
            throw new Exception('You are not allowed to access this page');
        }

        $p = $_GET['p'] ?? '';
        $f = $_GET['f'] ?? '';

        $f = FileSystem::cleanPath($f);

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        if ($f != '' && is_file($targetPath)) {
            Utils::download($targetPath, $f, 1024 * 10, true);
            exit;
        } else {
            App::$session->notify('g-danger', 'File not found');
        }
    }

    /**
     * @throws Exception
     */
    public function actionNewAjax(): false|string
    {
        session_start();

        $jsonResponse = new JsonResponse('error', 'YOU MUST BE LOGGED IN AND AJAX REQUIRED.');

        if (!$this->isAjax() || App::$session->isGuest()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $p = $_GET['p'] ?? '';
        $t = $_GET['t'] ?? 'file';

        if ($p != '') {
            $p = base64_decode($p);
        }

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            $data['name'] = str_replace('/', '', FileSystem::cleanPath(strip_tags($data['name'])));

            if (empty($data["name"])) {
                $jsonResponse->addError('name', 'Name is required.');
            }

            if (!FileSystem::isValidFilename($data["name"])) {
                $jsonResponse->addError('name', 'Invalid characters in file or folder name.');
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['name'];

            if ($t == 'file') {
                if (file_exists($targetPath)) {
                    $jsonResponse->addError('name', 'File already exists.');
                    return $this->asJson($jsonResponse->json(400));
                }

                @fopen($targetPath, 'w') or die('Cannot open file:  ' . $data['name']);

                App::$session->notify('g-success', 'File created successfully');

                $jsonResponse->set('success', 'File created successfully.');
                return $this->asJson($jsonResponse->json());
            } else {
                if (FileSystem::mkdir($targetPath, false) === true) {
                    App::$session->notify('g-success', 'Folder created successfully');

                    $jsonResponse->set('success', 'Folder created successfully.');
                    return $this->asJson($jsonResponse->json());
                }

                if (FileSystem::mkdir($targetPath, false) === $targetPath) {
                    $jsonResponse->addError('name', 'Folder already exists.');
                    return $this->asJson($jsonResponse->json(400));
                }

                $jsonResponse->addError('name', 'Folder not created.');
                return $this->asJson($jsonResponse->json(400));
            }
        }

        return $this->renderPartial('_new', [
            'action' => Utils::urlTo('site/new-ajax?p=' . base64_encode($p) . '&t=' . $t),
            'type' => $t,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionRename(): false|string
    {
        session_start();

        $jsonResponse = new JsonResponse('error', 'YOU MUST BE LOGGED IN AND AJAX REQUIRED.');

        if (!$this->isAjax() || App::$session->isGuest()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $p = $_GET['p'] ?? '';
        $old = $_GET['f'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }
        if ($old != '') {
            $old = base64_decode($old);
        }

        if ($this->isPost()) {
            $data = $this->getJsonInput() ?? $_POST;

            $old = str_replace('/', '', FileSystem::cleanPath(strip_tags($old)));
            $data['newName'] = str_replace('/', '', FileSystem::cleanPath(strip_tags($data['newName'])));

            if (empty($data["newName"])) {
                $jsonResponse->addError('newName', 'Name is required.');
            }
            if (!FileSystem::isValidFilename($data["newName"])) {
                $jsonResponse->addError('newName', 'Invalid characters in new file or folder name.');
            }
            if ($old == $data["newName"]) {
                $jsonResponse->addError('newName', 'You cannot specify the same name.');
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            $targetOldPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $old;
            $targetNewPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['newName'];

            App::$logger->info("Renaming $p ($old to {$data['newName']})");
            if (FileSystem::rename($targetOldPath, $targetNewPath)) {
                App::$session->notify('g-success', 'File or folder renamed successfully');

                $jsonResponse->set('success', 'File or folder renamed successfully.');
                return $this->asJson($jsonResponse->json());
            }

            $jsonResponse->addError('newName', 'Error while renaming. The file may already exist.');
            return $this->asJson($jsonResponse->json(400));
        }

        return $this->renderPartial('_rename', [
            'action' => Utils::urlTo('site/rename?p=' . base64_encode($p) . '&f=' . base64_encode($old)),
            'oldName' => $old,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionView(): string
    {
        session_start();

        if (App::$session->isGuest()) {
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

        $file = FileSystem::cleanPath($file);

        $path = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);

        $targetPath = $path . DIRECTORY_SEPARATOR . $file;

        if ($file == '' || !is_file($targetPath)) {
            App::$session->notify('g-danger', 'File not found');
            $this->redirect('site/index');
        }

        $type = FileSystem::typeFile($targetPath);

        $objects = is_readable($path) ? scandir($path) : [];

        $files = [];

        $extensionsByType = FileSystem::extensionByType($type);

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
            'pageTitle' => 'View',
            'p' => $p,
            'file' => $file,
            'type' => $type,
            'url' => Utils::urlTo('api/image?image=' . base64_encode($targetPath)) . '&type=' . $type,
            'arrFiles' => $files,
            'filenames' => $filenames,
        ]);
    }

    public function getArrFile(string $new_path, mixed $p, mixed $f): array
    {
        $fs = filesize($new_path);

        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : FileSystem::fileIconClass($new_path),
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'filesize_raw' => $fs,
            'filesize' => FileSystem::filesize($fs),
            'link' => base64_encode($p) . '&amp;view=' . base64_encode($f),
            'f' => $f,
            'encFile' => Utils::enc($f),
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
            'encFile' => Utils::enc($f)
        ];
    }

    /**
     * @throws Exception
     */
    public function actionDelete(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            throw new Exception('You are not allowed to access this page');
        }

        $p = $_GET['p'] ?? '';
        $f = $_GET['f'] ?? '';
        $t = $_GET['t'] ?? 'file';
        $cardId = $_GET['cardId'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }
        if ($f != '') {
            $f = base64_decode($f);
        }

        $f = FileSystem::cleanPath($f);

        return $this->renderPartial('_delete', [
            'action' => Utils::urlTo('api/delete'),
            'p' => $p,
            'f' => $f,
            'type' => $t,
            'cardId' => $cardId,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionHelp(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            return $this->redirect('auth/login');
        }

        return $this->render('help');
    }

    #[NoReturn] public function actionChangeTheme(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            $this->redirect('auth/login');
        }

        App::$system->updateConfig('theme', App::$system->isLightTheme() ? 'dark' : 'light');

        $this->redirect('site/index');
    }

    #[NoReturn] public function actionCompress(): void
    {
        session_start();

        if (App::$session->isGuest()) {
            $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';
        $f = $_GET['f'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }
        if ($f != '') {
            $f = base64_decode($f);
        }

        $zipName = basename($f) . '_' . date('ymd_His') . '.zip';

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        $zipFile = new Zip(App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $zipName);

        if ($zipFile->open()) {
            $zipFile->addFolder($targetPath);
            $zipFile->close();
        }

        $this->redirect('site/index?p=' . base64_encode($p));
    }

    /**
     * @throws Exception
     */
    public function actionShare(): string
    {
        session_start();

        if (App::$session->isGuest()) {
            $this->redirect('auth/login');
        }

        $p = $_GET['p'] ?? '';
        $f = $_GET['f'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }
        if ($f != '') {
            $f = base64_decode($f);
        }

        $payload = [
            'iss' => 'jcarrasco96.com',
            'aud' => 'nas.jcarrasco96.org',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'p' => $p,
                'f' => $f,
            ],
        ];

        $jwt = JWT::encode($payload, App::$config['jwtSecretKey'], 'HS256');

        return $this->renderPartial('_share', [
            'jwt' => $jwt,
        ]);

    }

}