<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('America/Havana');

//ignore_user_abort(true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'log/error_' . date('Ymd') . '.log');

set_exception_handler(['app\core\ExceptionHandler', 'handleException']);

$config = require_once 'app/config.php';

$app = new app\core\App($config);

// auth get
$app->get('/^auth\/login$/', 'login');
// auth post
$app->post('/^auth\/login$/', 'login');
$app->post('/^auth\/logout$/', 'logout');

$base64Regex = "([A-Za-z0-9+\/]+={0,2})";

// site get
$app->get('/^site\/index$/', 'index');
$app->get("/^site\/index\/$base64Regex$/", 'index');
$app->get("/^site\/upload-link$/", 'uploadLink');
$app->get('/^site\/new\/(file|folder)$/', 'new');
$app->get("/^site\/rename\/$base64Regex$/", 'rename');
$app->get("/^site\/download\/$base64Regex/", 'download');
$app->get("/^site\/view\/$base64Regex$/", 'view');
$app->get("/^site\/delete\/$base64Regex$/", 'delete');
$app->get('/^site\/help$/', 'help');
$app->get('/^site\/change-theme$/', 'changeTheme');
$app->get("/^site\/compress\/$base64Regex/", 'compress');
$app->get("/^site\/share\/$base64Regex/", 'share');
$app->get('/^site\/settings$/', 'settings');
$app->get("/^site\/copy\/(file|folder)\/$base64Regex/", 'copy');
// site post
$app->post("/^site\/new\/$base64Regex$/", 'new');
$app->post("/^site\/rename\/$base64Regex$/", 'rename');
$app->post('/^site\/settings$/', 'settings');

// api get
$app->get('/^api\/dd$/', 'directDownload');
$app->get('/^api\/raw$/', 'raw');
// api post
$app->post('/^api\/upload$/', 'upload');
$app->post('/^api\/delete$/', 'delete');
$app->post('/^api\/upload-link$/', 'uploadLink');
$app->post('/^api\/copy$/', 'copy');

// admin get
$app->get('/^admin\/users$/', 'users');
$app->get('/^admin\/users\/new$/', 'newUser');
$app->get('/^admin\/users\/([0-9]+)\/delete$/', 'delete');
$app->get('/^admin\/users\/([0-9]+)\/change-password$/', 'changePassword');
// admin post
$app->post('/^admin\/users\/new$/', 'newUser');
$app->post('/^admin\/users\/([0-9]+)\/delete$/', 'delete');
$app->post('/^admin\/users\/([0-9]+)\/c([A-Za-z]+)$/', 'changeAttribute');
$app->post('/^admin\/users\/([0-9]+)\/change-password$/', 'changePassword');

$app->run();
