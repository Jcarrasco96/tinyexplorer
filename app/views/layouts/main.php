<?php

/** @var string $content */
/** @var string $pageTitle */

use app\core\App;
use app\helpers\Alert;
use app\helpers\Html;

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= App::$system->theme ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - <?= App::$config['name'] ?></title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <?= Html::icon("img/favicon.png") ?>
    <?= Html::icon("img/apple-touch-icon.png", "apple-touch-icon") ?>

    <?= Html::css("bootstrap.min.css") ?>
    <?= Html::css("bootstrap-icons/bootstrap-icons.min.css") ?>
    <?= Html::css("animate.css") ?>
    <?= Html::css("dropzone.css") ?>
    <?= Html::css("preloader.css") ?>
    <?= Html::css("style.css") ?>

    <?= Html::js("jquery-3.7.1.min.js") ?>
    <?= Html::js("dropzone-min.js") ?>
</head>
<body>

<?php if (App::$session->isLoggedIn()): ?>
    <?php include_once VIEWS . 'layouts/_nav.php'; ?>
<?php endif; ?>

<div class="wrapper">
    <main id="content" class="content" style="display: none;">
        <?= $content; ?>
    </main>

    <div class="container">
        <footer class="d-flex flex-wrap justify-content-between align-items-center pt-3 my-3 border-top">
            <div class="col-md-6 d-flex align-items-center">
            <span class="mb-3 mb-md-0 text-body-secondary">
                © 2025 JC IT NETWORK, LLC
                <br>
                This software is licensed under the <a href="https://github.com/Jcarrasco96/tinyexplorer/blob/master/LICENSE">AGPL-3.0</a>. Source code is available <a href="https://github.com/Jcarrasco96/tinyexplorer" target="_blank">here</a>.
            </span>
            </div>

            <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
                <li class="ms-3"><a class="text-body-secondary" href="https://github.com/Jcarrasco96/tinyexplorer/issues"><i class="bi bi-bug"></i> Report issue</a></li>
            </ul>
        </footer>
    </div>
</div>



<div class="preloader"></div>
<button class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("fix.container.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("growl-notification-bootstrap-alert/bootstrap-notify.min.js") ?>
<?= Html::js("notify.js") ?>

<?= Alert::run() ?>

<?php include_once VIEWS . 'layouts/_modal.php'; ?>

</body>
</html>
