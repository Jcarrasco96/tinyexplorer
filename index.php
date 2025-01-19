<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('America/Havana');

require_once 'app/exception_handler.php';

set_exception_handler('exception_handler');

$config = require_once 'app/config.php';

$is_https = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
defined('SELF_URL') || define('SELF_URL', ($is_https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
const APP_PATH = ROOT . 'app' . DIRECTORY_SEPARATOR;
const VIEWS = APP_PATH . 'views' . DIRECTORY_SEPARATOR;

$app = new app\core\App($config);

// auth get
$app->get('/^auth\/login$/', 'login');
// auth post
$app->post('/^auth\/login$/', 'login');
$app->post('/^auth\/logout$/', 'logout');

// site get
$app->get('/^site\/index$/', 'index');
$app->get('/^site\/index\/.+$/', 'index');
$app->get('/^site\/image$/', 'image');
$app->get('/^site\/upload$/', 'upload');
$app->get('/^site\/new-ajax$/', 'newAjax');
$app->get('/^site\/rename$/', 'rename');
$app->get('/^site\/download$/', 'download');
$app->get('/^site\/view$/', 'view');
$app->get('/^site\/delete$/', 'delete');
$app->get('/^site\/help$/', 'help');
$app->get('/^site\/change-theme$/', 'changeTheme');
$app->get('/^site\/compress$/', 'compress');
$app->get('/^site\/share$/', 'share');
// site post
$app->post('/^site\/new-ajax$/', 'newAjax');
$app->post('/^site\/rename$/', 'rename');

// api get
$app->get('/^api\/dd$/', 'directDownload');
$app->get('/^api\/image$/', 'image');
// api post
$app->post('/^api\/upload$/', 'upload');
$app->post('/^api\/delete$/', 'delete');

$app->run();
