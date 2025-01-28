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
    public function actionIndex(string $p = ''): string
    {
        $this->ensureAuthenticated();

        list($p, $path) = $this->cleanPath($p);

        $parent = FileSystem::parentPath($p);

        list($files, $folders) = $this->filesAndFoldersInPath($path, $_GET['s'] ?? '');

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
    public function actionDownload(string $file): void
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cDownload')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        $p = App::$session->path(true);

        $f = FileSystem::cleanPath(base64_decode($file));

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        if ($f != '' && is_file($targetPath)) {
            Utils::download($targetPath, $f, 1024 * 10, true);
            exit;
        } else {
            App::$session->notify('g-danger', App::t('File {path} not found.', [$f]));
        }
    }

    /**
     * @throws Exception
     */
    public function actionNew(string $type): false|string
    {
//        if (!App::$session->getPermission('cAdmin')) {
//            App::$session->notify('g-warning', 'Not allowed to access this page.');
//            $this->redirect('site/index');
//        }

        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax() || App::$session->isGuest()) {
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
    public function actionRename(string $file): false|string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!App::$session->getPermission('cRename')) {
            $jsonResponse->set('error', 'Not allowed to access this page.');
            return $this->asJson($jsonResponse->json(400));
        }

        if (!$this->isAjax() || App::$session->isGuest()) {
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
    public function actionView(string $file): string
    {
        $this->ensureAuthenticated();

//        if (!App::$session->getPermission('cDownload')) {
//            App::$session->notify('g-warning', 'Not allowed to access this page.');
//            $this->redirect('site/index');
//        }

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
                    $files[] = $this->getArrFile($new_path, $f);
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
            'url' => Utils::urlTo('api/raw?file=' . base64_encode($targetPath)) . '&type=' . $type,
            'arrFiles' => $files,
            'filenames' => $filenames,
        ]);
    }

    public function getArrFile(string $new_path, mixed $f): array
    {
        $fs = filesize($new_path);

        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : FileSystem::fileIconClass($new_path),
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'filesize_raw' => $fs,
            'filesize' => FileSystem::filesize($fs),
            'link' => App::$session->path() . '&amp;view=' . base64_encode($f),
            'f' => $f,
            'encFile' => Utils::enc($f),
            'isFile' => true,
        ];
    }

    public function getArrFolder(string $new_path, mixed $f): array
    {
        return [
            'is_link' => is_link($new_path),
            'bi_icon' => is_link($new_path) ? 'bi bi-folder-symlink' : 'bi bi-folder',
            'modification_date' => date('m/d/Y h:i A', filemtime($new_path)),
            'link' => base64_encode(trim(App::$session->path(true) . '/' . $f, '/')),
            'f' => $f,
            'encFile' => Utils::enc($f),
            'isFile' => false,
        ];
    }

    /**
     * @throws Exception
     */
    public function actionDelete(string $file): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cDelete')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

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
    public function actionHelp(): string
    {
        $time_start = microtime(true);

        $this->ensureAuthenticated();

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
    public function actionChangeTheme(): string
    {
        $this->ensureAuthenticated();

        $theme = App::$system->isLightTheme() ? 'dark' : 'light';

        App::$system->updateConfig('theme', $theme);
        App::$logger->info('Theme changed: ' . $theme);

        return $this->asJson(['success' => true]);
    }

    /**
     * @throws Exception
     */
    #[NoReturn] public function actionCompress(string $file): void
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cCompress')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

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
    public function actionShare(string $file): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cShare')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

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
    public function actionSettings(): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cAdmin')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

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

    function scanDirectory(string $p, string $directory, string $filter): array
    {
        $result = [];

//        if ($handle = opendir($directory)) {
//            while (false !== ($item = readdir($handle))) {
//                if ($item == '.' || $item == '..' || str_starts_with($item, '.') || $item == 'System Volume Information' || $item == '$RECYCLE.BIN') {
//                    continue;
//                }
//                $path = $directory . DIRECTORY_SEPARATOR . $item;
//                if (is_file($path) && str_contains(strtolower($item), strtolower($filter))) {
//                    $result[] = $this->getArrFile($path, $p, $item);
//                } elseif (is_dir($path) && is_readable($path)) {
//                    $result = array_merge($result, $this->scanDirectory($p, $path, $filter));
//                }
//            }
//            closedir($handle);
//        }

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..' || str_starts_with($item, '.') || $item == 'System Volume Information' || $item == '$RECYCLE.BIN') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_file($path) && str_contains(strtolower($item), strtolower($filter))) {
                $result[] = $this->getArrFile($path, $p, $item);
            } elseif (is_dir($path) && is_readable($path)) {
                $result = array_merge($result, $this->scanDirectory($p, $path, $filter));
            }
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function actionUploadLink(): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cUpload')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

        $p = App::$session->path(true);

        return $this->render('upload-link', [
            'p' => $p,
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionCopy(string $type, string $file): string
    {
        $this->ensureAuthenticated();

        if (!App::$session->getPermission('cCopy')) {
            App::$session->notify('g-warning', 'Not allowed to access this page.');
            $this->redirect('site/index');
        }

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

        $parent = FileSystem::parentPath($p);

        return $this->render('copy', [
            'p' => $p,
            'f' => $file,
            'parent' => $parent,
            'file' => $targetPath,
            'arrFolders' => $this->foldersInPath($path),
            'type' => $type,
        ]);
    }

    public function cleanPath(string $p = ''): array
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
            $this->redirect('site/index');
        }

        return [$p, $path];
    }

    private function foldersInPath(string $path): array
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

                $folders[] = $this->getArrFolder($new_path, $vFile);
            }
        }

        if (!empty($folders)) {
            usort($folders, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        return $folders;
    }

    private function filesAndFoldersInPath(string $path, string $search = null): array
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
                    $files[] = $this->getArrFile($new_path, $file);
                } elseif (@is_dir($new_path)) {
                    $folders[] = $this->getArrFolder($new_path, $file);
                }
            }
        }

        if (!empty($files)) {
            usort($files, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }
        if (!empty($folders)) {
            usort($folders, fn ($a, $b) => strcasecmp($a['f'], $b['f']));
        }

        return [$files, $folders];
    }

}