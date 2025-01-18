<?php

/** @var ?string $p */
/** @var string $file */
/** @var string $type */
/** @var string $url */
/** @var array $arrFiles */
/** @var array $filenames */

use app\core\Alert;
use app\core\App;
use app\core\Breadcrumb;
use app\core\Html;
use app\core\Utils;

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Index - <?= App::$config['name'] ?></title>
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
</head>
<body>

<?php include_once VIEWS . 'layouts/_nav.php'; ?>

<main id="content" style="display: none;">

    <div class="container">

        <div class="d-flex align-items-center mb-2">
            <?php if ($p !== false): ?>
                <nav aria-label="breadcrumb" class="me-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index?p=' .  urlencode($p)) ?>"><i class="bi bi-chevron-left"></i></a></li>
                    </ol>
                </nav>

                <?= Breadcrumb::run(['path' => $p . '/' . $file]) ?>
            <?php else: ?>
                <nav aria-label="breadcrumb" class="me-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a></li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <?php if ($type == 'video'): ?>
                    <div class="preview-video">
                        <video controls src="<?= $url ?>" preload="metadata" playsinline class="borders roundeds">
                            <source src="<?= $url ?>" type="video/mp4">
                        </video>
                    </div>
                    <h4 class="display-6"><?= $file ?></h4>
                <?php elseif ($type == 'image'): ?>
                    <div class="preview-video">
                        <img class="img-thumbnail" src="<?= $url ?>" alt="" />
                    </div>
                <?php elseif ($type == 'audio'): ?>
                    <audio controls src="<?= $url ?>" preload="metadata" class="w-100"></audio>
                <?php elseif ($type == 'application'): ?>
                    <object style="width: 100%; height: 80vh;" data="<?= $url ?>"></object>

                    <?php if ($filenames): ?>
                        <?php foreach ($filenames as $filename): ?>
                            <code><?= $filename['name'] ?> (<?= Utils::fmGetFilesize($filename['compressed_size']) ?>)</code><br>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-3">
                <div class="row row-cols-1 g-1">
                    <?php foreach ($arrFiles as $kFile => $vFile): ?>
                        <div class="col">
                            <div class="card" id="card-file" data-url="<?= Utils::urlTo('site/view?p=' . $vFile['link']) ?>">
                                <div class="card-body p-2">
                                    <h5 class="mb-0"><i class="<?= $vFile['bi_icon'] ?>"></i> <?= Utils::fmConvertWin($vFile['encFile']) ?></h5>
                                    <small class="text-body-secondary me-auto"><?= $vFile['filesize'] ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

</main>

<style>
    .preview-video {
        /*background-color: #0d6efd;*/
        /*width: 100%;*/
        /*max-width: 100%;*/
        overflow: hidden;
        text-align: center;
    }
    .preview-video video {
        max-width: 100%;
        max-height: 75vh;
        width: auto;
        height: auto;
        object-fit: cover;
        /*display: block;*/
    }
    .preview-video img {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
    }
</style>

<div class="preloader"></div>
<button class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<?= Html::js("jquery-3.7.1.min.js") ?>
<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("fix.container.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("growl-notification-bootstrap-alert/bootstrap-notify.min.js") ?>
<?= Html::js("notify.js") ?>
<?= Html::js("dropzone-min.js") ?>

<?= Alert::run() ?>

<?php include_once VIEWS . 'layouts/_modal.php'; ?>

<script>
    $(document).on("click", "#card-file", function (event) {
        event.preventDefault();

        let url = $(this).data('url');

        window.location.assign(url);

        return false;
    });
</script>

</body>
</html>