<?php

namespace TE\controllers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use TE\core\App;
use TE\core\BaseController;
use TE\core\ControllerPermission;
use TE\helpers\Utils;
use TE\http\JsonResponse;
use TE\services\FileSystem;

class ApiController extends BaseController
{

    /**
     * @throws Exception
     */
    #[ControllerPermission(['@'])]
    public function actionRaw(): string|false
    {
        $path = App::$system->rootPath . DIRECTORY_SEPARATOR . base64_decode($_GET['file']);

        if (!file_exists($path)) {
            throw new Exception(App::t('File {path} not found.', [$path]));
        }

        $mimeType = FileSystem::fileTypes($path);

        header("Content-Type: $mimeType");
        header("Content-Disposition: inline; filename=\"$path\"");

        readfile($path);
        exit;
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['*'])]
    public function actionDirectDownload(): void
    {
        $jwt = $_GET['t'] ?? '';

        if (empty($jwt)) {
            throw new Exception(App::t('You are not allowed to access this page.'));
        }

        try {
            $decoded = JWT::decode($jwt, new Key(App::$config['jwtSecretKey'], 'HS256'));

            if ($decoded->iss != 'jcarrasco96.com' || $decoded->aud != 'nas.jcarrasco96.org') {
                throw new Exception('JWT is not valid.');
            }

            $f = FileSystem::cleanPath($decoded->data->f);

            $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $decoded->data->p) . DIRECTORY_SEPARATOR . $f;

            if ($f != '' && is_file($targetPath)) {
                Utils::download($targetPath, $f, 1024 * 10, true);
                exit;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        throw new Exception(App::t('File {path} not found.', [$f]));
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cUpload', 'cAdmin'])]
    public function actionUpload(): string
    {
        $jsonResponse = new JsonResponse('error', App::t('Error while uploading files.'));

        $p = App::$session->path(true);

        $tmp_name = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) != '' ? strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) : '';

        $fullPathInput = FileSystem::cleanPath($_POST['fullpath']);
        $folderPath = App::$system->rootPath . '/' . $p . '/';

        if (is_writable($folderPath) || FileSystem::isNAS(App::$system->rootPath)) {
            $fullPath = $folderPath . basename($fullPathInput);

            if ($_POST['dztotalchunkcount']) {
                if ($out = @fopen("$fullPath.part", $_POST['dzchunkindex'] == 0 ? "wb" : "ab")) {
                    if ($in = @fopen($tmp_name, "rb")) {
                        if (PHP_VERSION_ID < 80009) {
                            // workaround https://bugs.php.net/bug.php?id=81145
                            do {
                                while (($buff = fread($in, 4096))) {
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

                    $jsonResponse->set('success', App::t('File by chunk upload successful.'));
                } else {
                    $jsonResponse->set('error', App::t('Failed to open output stream.'));
                }

                if ($_POST['dzchunkindex'] == $_POST['dztotalchunkcount'] - 1) {
                    $fullPathTarget = $fullPath;

                    if (file_exists($fullPath)) {
                        $ext_1 = $ext ? '.' . $ext : '';
                        $fullPathTarget = App::$system->rootPath . $p . '/' . basename($fullPathInput, $ext_1) . '_' . date('ymdHis') . $ext_1;
                    }

                    rename("$fullPath.part", $fullPathTarget);
                }

            } else if (move_uploaded_file($tmp_name, $fullPath)) {
                // Be sure that the file has been uploaded
                if (file_exists($fullPath)) {
                    $jsonResponse->set('success', App::t('File upload successful.'));
                } else {
                    $jsonResponse->set('error', App::t("Couldn't upload the requested file."));
                }
            }
        } else {
            $jsonResponse->set('error', App::t("Folder $folderPath is not writable."));
        }

        return $this->asJson($jsonResponse->json(400));
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cDelete', 'cAdmin'])]
    public function actionDelete(): string|false
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $this->validateCsrf('site/login');

        $data = $this->getPostData();

        $p = App::$session->path(true);

        $f = isset($data['f']) ? base64_decode($data['f']) : '';

        $cardId = $data['cardId'] ?? '';

        $f = FileSystem::cleanPath($f);

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        if ($f == '' || $f == '..' || $f == '.') {
            $jsonResponse->set('error', App::t('File {path} not found.', [$f]));
            return $this->asJson($jsonResponse->json(400));
        }

        $isDir = is_dir($targetPath);

        if (FileSystem::realDelete($targetPath)) {
            $jsonResponse->data['cardId'] = $cardId;
            $jsonResponse->set('success', $isDir ? App::t('Folder deleted successfully.') : App::t('File deleted successfully.'));
        } else {
            $jsonResponse->set('error', $isDir ? App::t('Deleting folder failed.') : App::t('Deleting file failed.'));
        }

        return $this->asJson($jsonResponse->json(400));
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cUpload', 'cAdmin'])]
    public function actionUploadLink(): string
    {
        $p = isset($_GET['p']) ? base64_decode($_GET['p']) : '';

        $path = App::$system->rootPath;
        if ($p != '') {
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            return $this->asJson(['status' => 'error', 'message' => App::t('Path {path} is not a directory.', [$path])]);
        }

        $data = $this->getPostData();

        $url = preg_match("|^http(s)?://.+$|", stripslashes($data["directLink"])) ? stripslashes($data["directLink"]) : null;

        $domain = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $knownPorts = [22, 23, 25, 3306];

        if (preg_match("/^localhost$|^127(?:\.[0-9]+){0,2}\.[0-9]+$|^(?:0*:)*?:?0*1$/i", $domain) || in_array($port, $knownPorts)) {
            return $this->asJson(['status' => 'error', 'message' => App::t('URL not allowed.')]);
        }

        if (!$url) {
            return $this->asJson(['status' => 'error', 'message' => App::t('URL cannot be empty.')]);
        }

        $temp_file = tempnam(sys_get_temp_dir(), "te-");

        $name = trim(basename($url), ".\x00..\x20");

        $ext = pathinfo($name, PATHINFO_FILENAME) != '' ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : '';
        $ext_1 = $ext ? '.' . $ext : '';

        if (App::$system->isCurl()) {
            $fp = @fopen($temp_file, "w");
            $ch = @curl_init($url);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = @curl_exec($ch);
            @curl_close($ch);
            fclose($fp);

            if (!$success) {
                $msg = curl_error($ch);
            }
        } else {
            $success = @copy($url, $temp_file, stream_context_create());
            if (!$success) {
                $msg = error_get_last();
            }
        }

        if (!$success) {
            return $this->asJson(['status' => 'error', 'message' => $msg]);
        }

        if (rename($temp_file, strtok($path . DIRECTORY_SEPARATOR . basename($name, $ext_1) . '_' . date('ymdHis') . $ext_1, '?'))) {
            return $this->asJson(['status' => 'success', 'message' => App::t('Upload file successfully.')]);
        }

        @unlink($temp_file);

        return $this->asJson(['status' => 'error', 'message' => App::t('Invalid url parameter.')]);
    }

    /**
     * @throws Exception
     */
    #[ControllerPermission(['cCopy', 'cAdmin'])]
    public function actionCopy(): false|string
    {
        $jsonResponse = new JsonResponse('error', App::t('YOU MUST BE LOGGED IN AND AJAX REQUIRED.'));

        if (!$this->isAjax()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $data = $this->getPostData();

        $p = isset($data['p']) ? base64_decode($data['p']) : '';
        $f = isset($data['f']) ? base64_decode($data['f']) : '';

        if (!isset($data['copy']) || !in_array($data['copy'], ['copy', 'move'])) {
            $jsonResponse->set('error', App::t('Method not found.'));
            return $this->asJson($jsonResponse->json(400));
        }

        if (!isset($data['t']) || !in_array($data['t'], ['folder', 'file'])) {
            $jsonResponse->set('error', App::t('Type not found.'));
            return $this->asJson($jsonResponse->json(400));
        }

        $type = $data['t'];
        $isCopy = $data['copy'] == 'copy';

        $file = FileSystem::cleanPath($f);

        $targetPath = App::$system->rootPath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        if ($type === 'file' && ($file == '' || !is_file($targetPath))) {
            $jsonResponse->set('error', App::t('File {path} not found.', [$targetPath]));
            return $this->asJson($jsonResponse->json(400));
        }

        if ($type === 'folder' && ($file == '' || !is_dir($targetPath))) {
            $jsonResponse->set('error', App::t('Folder {path} not found.', [$targetPath]));
            return $this->asJson($jsonResponse->json(400));
        }

        $path = App::$system->rootPath;
        if ($p != '') {
            $path .= '/' . $p;
        }

        if (!is_dir($path) || str_contains($path, '$RECYCLE.BIN')) {
            $jsonResponse->set('error', App::t('File {path} not found.', [$targetPath]));
            return $this->asJson($jsonResponse->json(404));
        }

        $destPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . basename($targetPath);

        App::$logger->notice("Copying from $targetPath to $destPath");

        if ($destPath == $targetPath && !$isCopy) {
            $jsonResponse->set('error', App::t('Paths must be not equal.'));
            return $this->asJson($jsonResponse->json(400));
        }

        if ($destPath == $targetPath && $isCopy) {
            $originFileInfo = pathinfo($targetPath);

            $duplicateName = $originFileInfo['dirname'] . DIRECTORY_SEPARATOR . $originFileInfo['filename'] . '-' . date('YmdHis') . '.' . $originFileInfo['extension'];

            if (FileSystem::recursiveCopy($targetPath, $duplicateName, false)) {
                $jsonResponse->set('success', App::t('File {$targetPath} has been copied.', [$targetPath]));
                return $this->asJson($jsonResponse->json());
            }
        }

        if ($destPath != $targetPath && !$isCopy) {
            if (file_exists($destPath)) {
                $originFileInfo = pathinfo($destPath);
                $destPath = $originFileInfo['dirname'] . DIRECTORY_SEPARATOR . $originFileInfo['filename'] . '-' . date('YmdHis') . '.' . $originFileInfo['extension'];
            }
            if (FileSystem::rename($targetPath, $destPath)) {
                $jsonResponse->set('success', 'File moved to ' . $destPath);
            } else {
                $jsonResponse->set('error', 'File not moved to ' . $destPath);
            }
            return $this->asJson($jsonResponse->json(400));
        }

        if ($destPath != $targetPath && $isCopy) {
            if (file_exists($destPath)) {
                $originFileInfo = pathinfo($destPath);
                $destPath = $originFileInfo['dirname'] . DIRECTORY_SEPARATOR . $originFileInfo['filename'] . '-' . date('YmdHis') . '.' . $originFileInfo['extension'];
            }

            if (FileSystem::recursiveCopy($targetPath, $destPath, false)) {
                $jsonResponse->set('success', App::t('File {$targetPath} has been copied.', [$targetPath]));
            } else {
                $jsonResponse->set('error', 'File not copied to ' . $destPath);
            }

            return $this->asJson($jsonResponse->json(400));
        }

        $jsonResponse->set('error', App::t('Error while copying from {target} to {destination}', [$targetPath, $destPath]));
        return $this->asJson($jsonResponse->json(400));
    }

    /**
     * @throws Exception
     */
//    #[ControllerPermission(['@'])]
    public function actionList(string $p = ''): string
    {
        list($p, $path) = Utils::cleanPath($p);

        $parent = FileSystem::parentPath($p);

        list($files, $folders) = FileSystem::filesAndFoldersInPath($path, $_GET['s'] ?? '');

        return $this->asJson([
            'p' => $p,
            'arrFolders' => $folders,
            'arrFiles' => $files,
            'parent' => $parent,
        ]);
    }

}