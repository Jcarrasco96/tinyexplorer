<?php

namespace TE\controllers;

use Exception;
use Firebase\JWT\JWT;
use JetBrains\PhpStorm\NoReturn;
use TE\core\App;
use TE\core\BaseController;
use TE\core\ControllerPermission;
use TE\helpers\Utils;
use TE\http\JsonResponse;
use TE\services\FileSystem;
use TE\services\Zip;
use ZipArchive;

class SiteController extends BaseController
{

    /**
     * @throws Exception
     */
    #[ControllerPermission(['@'])]
    public function actionIndex(string $p = ''): string
    {
        list($p, $path) = Utils::cleanPath($p);

        $parent = FileSystem::parentPath($p);

        list($files, $folders) = FileSystem::filesAndFoldersInPath($path, $_GET['s'] ?? '');

        return $this->render('index', [
            'p' => $p,
            'arrFolders' => $folders,
            'arrFiles' => $files,
            'parent' => $parent,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cDownload', 'cAdmin'])]
    public function actionDownload(string $file): void
    {
        $p = App::$session->path(true);

        $f = FileSystem::cleanPath(base64_decode($file));

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        if ($f != '' && is_file($targetPath)) {
            Utils::download($targetPath, $f, 1024 * 10);
            exit;
        } else {
            App::$session->notify('g-danger', App::t('File {path} not found.', [$f]));
        }
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cAdmin'])]
    public function actionNew(string $type): false|string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $p = App::$session->path(true);

        if ($this->isPost()) {
            $this->validateCsrf('site/login');

            $data = $this->getPostData();

            $data['name'] = str_replace('/', '', FileSystem::cleanPath(strip_tags($data['name'])));

            if (empty($data["name"])) {
                $jsonResponse->addError('name', App::t('Name is required.'));
            }

            if (!FileSystem::isValidFilename($data["name"])) {
                $jsonResponse->addError('name', App::t('Invalid characters in file or folder name.'));
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['name'];

            switch ($type) {

                case 'file':
                    if (file_exists($targetPath)) {
                        $jsonResponse->addError('name', App::t('File already exists.'));
                        return $this->asJson($jsonResponse->json(400));
                    }

                    @fopen($targetPath, 'w') or die('Cannot open file:  ' . $data['name']);

                    App::$session->notify('g-success', App::t('File created successfully.'));

                    $jsonResponse->set('success', App::t('File created successfully.'));
                    return $this->asJson($jsonResponse->json());

                case 'folder':
                    if (FileSystem::mkdir($targetPath, false) === true) {
                        App::$session->notify('g-success', App::t('Folder created successfully.'));

                        $jsonResponse->set('success', App::t('Folder created successfully.'));
                        return $this->asJson($jsonResponse->json());
                    }

                    if (FileSystem::mkdir($targetPath, false) === $targetPath) {
                        $jsonResponse->addError('name', App::t('Folder already exists.'));
                        return $this->asJson($jsonResponse->json(400));
                    }

                    $jsonResponse->addError('name', App::t('Folder not created.'));
                    return $this->asJson($jsonResponse->json(400));

            }
        }

        App::$session->generateCSRF(true);

        return $this->renderPartial('_new', [
            'action' => Utils::urlTo('site/new/' . $type),
            'type' => $type,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cRename', 'cAdmin'])]
    public function actionRename(string $file): false|string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $p = App::$session->path(true);

        if ($this->isPost()) {
            $this->validateCsrf('site/login');

            $data = $this->getPostData();

            $old = str_replace('/', '', FileSystem::cleanPath(strip_tags(base64_decode($file))));
            $data['newName'] = str_replace('/', '', FileSystem::cleanPath(strip_tags($data['newName'])));

            if (empty($data["newName"])) {
                $jsonResponse->addError('newName', App::t('Name is required.'));
            }
            if (!FileSystem::isValidFilename($data["newName"])) {
                $jsonResponse->addError('newName', App::t('Invalid characters in new file or folder name.'));
            }
            if ($old == $data["newName"]) {
                $jsonResponse->addError('newName', App::t('You cannot specify the same name.'));
            }

            if (!empty($jsonResponse->error)) {
                return $this->asJson($jsonResponse->json(400));
            }

            $targetOldPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $old;
            $targetNewPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $data['newName'];

            App::$logger->info("Renaming $p ($old to {$data['newName']})");
            if (FileSystem::rename($targetOldPath, $targetNewPath)) {
                App::$session->notify('g-success', App::t('File or folder renamed successfully.'));

                $jsonResponse->set('success', App::t('File or folder renamed successfully.'));
                return $this->asJson($jsonResponse->json());
            }

            $jsonResponse->addError('newName', App::t('Error while renaming. The file may already exist.'));
            return $this->asJson($jsonResponse->json(400));
        }

        App::$session->generateCSRF(true);

        return $this->renderPartial('_rename', [
            'file' => $file,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['@'])]
    public function actionView(string $file): string
    {
        $p = App::$session->path(true);

        $file = FileSystem::cleanPath(base64_decode($file));

        $path = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p);

        $targetPath = $path . DIRECTORY_SEPARATOR . $file;

        if ($file == '' || !is_file($targetPath)) {
            App::$session->notify('g-danger', App::t('File {path} not found.', [$file]));
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

                if (!in_array($ext, $extensionsByType)) { //  || $f == $file
                    continue;
                }

                $new_path = $path . '/' . $f;

                if (@is_file($new_path)) {
                    $files[] = FileSystem::getArrFile($new_path, $f);
                }
            }
        }

        if (!empty($files)) {
            usort($files, fn($a, $b) => strcasecmp($a['f'], $b['f']));
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
            'url' => Utils::urlTo('api/raw?file=' . base64_encode($targetPath)) . '&type=' . $type,
            'arrFiles' => $files,
            'filenames' => $filenames,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cDelete', 'cAdmin'])]
    public function actionDelete(string $file): string
    {
        $p = App::$session->path(true);

        $t = $_GET['t'] ?? 'file';
        $cardId = $_GET['cardId'] ?? '';

        $f = FileSystem::cleanPath(base64_decode($file));

        App::$session->generateCSRF(true);

        return $this->renderPartial('_delete', [
            'p' => $p,
            'f' => $f,
            'type' => $t,
            'cardId' => $cardId,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['*'])]
    public function actionHelp(): string
    {
        $time_start = microtime(true);

        $p = isset($_GET['p']) ? base64_decode($_GET['p']) : '';

        $s = $_GET['s'] ?? '';

        $path = App::$system->rootPath;
        if ($p != '') {
            $path .= '/' . $p;
        }

        $result = [];//$this->scanDirectory($path, $path, $s);

//        ini_set('max_execution_time', 300);

        return $this->render('help', [
            'result' => $result,
            'p' => $p,
            'parent' => FileSystem::parentPath($p),
            'execTime' => number_format(microtime(true) - $time_start, 4),
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['@'])]
    public function actionChangeTheme(): string
    {
        $theme = App::$system->isLightTheme() ? 'dark' : 'light';

        App::$system->updateConfig('theme', $theme);
        App::$logger->info('Theme changed: ' . $theme);

        return $this->asJson(['success' => true]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cCompress', 'cAdmin'])]
    #[NoReturn] public function actionCompress(string $file): void
    {
        $p = App::$session->path(true);
        $f = base64_decode($file);

        $zipName = basename($f) . '_' . date('ymd_His') . '.zip';

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        $zipFile = new Zip(App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $zipName);

        if ($zipFile->open()) {
            $zipFile->addFolder($targetPath);
            $zipFile->close();
        }

        $this->redirect('site/index/' . base64_encode($p));
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cShare', 'cAdmin'])]
    public function actionShare(string $file): string
    {
        $p = App::$session->path(true);

        $payload = [
//            'sub' => '1234567890',
            'iss' => 'jcarrasco96.com',
            'aud' => 'nas.jcarrasco96.org',
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'p' => $p,
                'f' => base64_decode($file),
            ],
        ];

        $jwt = JWT::encode($payload, App::$config['jwtSecretKey'], 'HS256');

        return $this->renderPartial('_share', [
            'jwt' => $jwt,
        ]);

    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cAdmin'])]
    public function actionSettings(): string
    {
        if ($this->isPost()) {
            $data = $this->getPostData();

            $this->validateCsrf('site/settings');

            if (isset($data['theme']) && in_array($data['theme'], ['dark', 'light'])) {
                App::$system->updateConfig('theme', $data['theme']);
                App::$logger->info('Theme changed: ' . $data['theme']);
            }
            if (isset($data['rootPath']) && is_dir($data['rootPath']) && is_readable($data['rootPath'])) {
                App::$system->updateConfig('root_path', FileSystem::cleanPath($data['rootPath']));
                App::$logger->info('Root path changed: ' . $data['rootPath']);
            } else {
                App::$session->notify('g-warning', 'Root path not found.');
            }
            if (isset($data['language']) && in_array($data['language'], ['en', 'es'])) {
                App::$system->updateConfig('language', $data['language']);
                App::$logger->info('Language changed: ' . $data['language']);
            }
            if (isset($data['useCurl']) && in_array(strtolower($data['useCurl']), ['y', 'n'])) {
                App::$system->updateConfig('use_curl', $data['useCurl']);
                App::$logger->info('CURL changed: ' . $data['useCurl']);
            }

            $this->redirect('site/settings');
        }

        App::$session->generateCSRF(true);

        return $this->render('settings');
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cUpload', 'cAdmin'])]
    public function actionUploadLink(): string
    {
        $p = App::$session->path(true);

        return $this->render('upload-link', [
            'p' => $p,
        ]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cCopy', 'cAdmin'])]
    public function actionCopy(string $type, string $file): string
    {
        $p = isset($_GET['p']) ? base64_decode($_GET['p']) : '';

        App::$session->setPath(base64_encode($p));

        $path = App::$system->rootPath;
        if ($p != '') {
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            $this->redirect('site/index');
        }

        $file = base64_decode($file);

        $targetPath = App::$system->rootPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        if ($type === 'file' && ($file == '' || !is_file($targetPath))) {
            App::$session->notify('g-danger', App::t('File {path} not found.', [$targetPath]));
            $this->redirect('site/index/' . base64_encode($p));
        }

        if ($type === 'folder' && ($file == '' || !is_dir($targetPath))) {
            App::$session->notify('g-danger', App::t('Folder {path} not found.', [$targetPath]));
            $this->redirect('site/index/' . base64_encode($p));
        }

        return $this->render('copy', [
            'p' => $p,
            'f' => $file,
            'parent' => FileSystem::parentPath($p),
            'file' => $targetPath,
            'arrFolders' => FileSystem::foldersInPath($path),
            'type' => $type,
        ]);
    }

}