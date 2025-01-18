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

// auth
$app->get('/^auth\/login$/', 'login');
//$app->get('/^auth\/register$/', 'register');

$app->post('/^auth\/login$/', 'login');
//$app->post('/^auth\/register$/', 'register');
$app->post('/^auth\/logout$/', 'logout');

// site
$app->get('/^site\/index$/', 'index');
$app->get('/^site\/index\/.+$/', 'index');
$app->get('/^site\/image$/', 'image');
$app->get('/^site\/upload$/', 'upload');
$app->get('/^site\/new-ajax$/', 'newAjax');
$app->get('/^site\/rename$/', 'rename');
$app->get('/^site\/download$/', 'download');
$app->get('/^site\/view$/', 'view');

$app->post('/^site\/upload$/', 'upload');
$app->post('/^site\/new-ajax$/', 'newAjax');
$app->post('/^site\/rename$/', 'rename');

$app->run();
