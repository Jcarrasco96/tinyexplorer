<?php

/** @var string $content */
/** @var string $pageTitle */

use app\core\App;
use app\helpers\Alert;
use app\helpers\Html;
use app\utils\Utils;

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= App::$system->theme ?>">
<head>
    <title><?= Utils::enc($pageTitle) ?> - <?= App::$config['name'] ?></title>

    <?= Html::css("dropzone.css") ?>

    <?php include_once '_head.php'; ?>

    <?= Html::js("dropzone-min.js") ?>
</head>
<body>

<?php include_once '_nav.php'; ?>

<div class="wrapper">
    <main id="content" class="content" style="display: none;">
        <?= $content; ?>
    </main>

    <?php include_once '_footer.php'; ?>
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
