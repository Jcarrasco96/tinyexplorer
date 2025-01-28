<?php

use app\helpers\Html;
use app\utils\Utils;

?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex">

<link rel="apple-touch-icon" sizes="180x180" href="<?= Utils::urlTo('assets/img/apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= Utils::urlTo('assets/img/favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= Utils::urlTo('assets/img/favicon-16x16.png') ?>">
<link rel="manifest" href="<?= Utils::urlTo('assets/img/site.webmanifest') ?>">

<?= Html::icon("img/favicon.png") ?>

<?= Html::css("bootstrap.min.css") ?>
<?= Html::css("bootstrap-icons/bootstrap-icons.min.css") ?>
<?= Html::css("animate.css") ?>
<?= Html::css("preloader.css") ?>
<?= Html::css("style.css") ?>
<?= Html::js("jquery-3.7.1.min.js") ?>
