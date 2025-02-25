<?php

use TE\core\App;
use TE\helpers\Alert;
use TE\helpers\Html;
use TE\helpers\Utils;

/** @var string $content */
/** @var string $pageTitle */

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= App::$system->theme ?>">
<head>
    <title><?= Utils::enc($pageTitle) ?> - <?= App::$config['name'] ?></title>
    <?php include_once '_head.php'; ?>
</head>
<body>

<main id="content" class="content" style="display: none;">
    <?= $content; ?>
</main>

<div class="preloader"></div>
<button class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("growl-notification-bootstrap-alert/bootstrap-notify.min.js") ?>
<?= Html::js("notify.js") ?>

<?= Alert::run() ?>

</body>
</html>
