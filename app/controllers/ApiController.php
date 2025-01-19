<?php

namespace app\controllers;

use app\core\App;
use app\core\BaseController;
use app\http\JsonResponse;
use app\services\FileSystem;
use app\utils\Utils;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends BaseController
{

    /**
     * @throws Exception
     */
    public function actionImage(): string|false
    {
        $path = base64_decode($_GET['image']);
        $type = $_GET['type'] ?? 'image';

        if (!file_exists($path)) {
            throw new Exception("File $path not found.");
        }

        header("Content-Type: $type/" . strtolower(pathinfo($path, PATHINFO_EXTENSION)));
        readfile($path);
        exit;
    }

    /**
     * @throws Exception
     */
    public function actionDirectDownload(): void
    {
        $jwt = $_GET['t'] ?? '';

        if (empty($jwt)) {
            throw new Exception('You are not allowed to access this page');
        }

        try {
            $decoded = JWT::decode($jwt, new Key(App::$config['jwtSecretKey'], 'HS256'));

            $f = FileSystem::cleanPath($decoded->data->f);

            $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $decoded->data->p) . DIRECTORY_SEPARATOR . $f;

            if ($f != '' && is_file($targetPath)) {
                Utils::download($targetPath, $f, 1024 * 10, true);
                exit;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        throw new Exception("File $f not found.");
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

        $response = new JsonResponse('error', 'Error while uploading files.');

        $tmp_name = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) != '' ? strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) : '';

        $targetPath = App::$system->rootPath . str_replace('/', DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR;

        if (is_writable($targetPath)) {
            $fullPathInput = FileSystem::cleanPath($_POST['fullpath']);
            $fullPath = App::$system->rootPath . $p . '/' . basename($fullPathInput);

            if ($_POST['dztotalchunkcount']) {
                if ($out = @fopen("$fullPath.part", $_POST['dzchunkindex'] == 0 ? "wb" : "ab")) {
                    if ($in = @fopen($tmp_name, "rb")) {
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

                    $response->set('success', 'File by chunk upload successful.');
                } else {
                    $response->set('error', 'Failed to open output stream.');
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
                    $response->set('success', 'File upload successful.');
                } else {
                    $response->set('error', "Couldn't upload the requested file.");
                }
            }
        }

        return $this->asJson($response->json());
    }

    /**
     * @throws Exception
     */
    public function actionDelete(): string|false
    {
        session_start();

        $jsonResponse = new JsonResponse('error', 'YOU MUST BE LOGGED IN AND AJAX REQUIRED.');

        if (!$this->isAjax() || App::$session->isGuest()) {
            return $this->asJson($jsonResponse->json(400));
        }

        $data = $this->getJsonInput() ?? $_POST;

        $p = $data['p'] ?? '';
        $f = $data['f'] ?? '';
        $cardId = $data['cardId'] ?? '';

        if ($p != '') {
            $p = base64_decode($p);
        }
        if ($f != '') {
            $f = base64_decode($f);
        }

        $f = FileSystem::cleanPath($f);

        $targetPath = App::$system->rootPath . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p) . DIRECTORY_SEPARATOR . $f;

        if ($f != '' && $f != '..' && $f != '.') {
            $isDir = is_dir($targetPath);

            if (FileSystem::realDelete($targetPath)) {
                $jsonResponse->data['cardId'] = $cardId;
                $jsonResponse->set('success', $isDir ? "Folder deleted successfully." : "File deleted successfully.");
            } else {
                $jsonResponse->set('error', $isDir ? "Deleting folder failed." : "Deleting file failed.");
            }
        } else {
            $jsonResponse->set('error', 'File not found.');
        }

        return $this->asJson($jsonResponse->json(400));
    }

}