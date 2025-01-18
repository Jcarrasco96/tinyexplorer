<?php

/** @var string $p */

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

    <title>Upload - <?= App::$config['name'] ?></title>
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
            <nav aria-label="breadcrumb" class="me-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index?p=' . $p) ?>"><i class="bi bi-chevron-left"></i></a></li>
                </ol>
            </nav>
        </div>

        <div class="my-dropzone dropzone">
            <div class="dz-message text-body-tertiary">Drag your files here or click to upload.</div>
        </div>

    </div>

</main>

<div class="preloader"></div>
<button class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<?= Html::js("jquery-3.7.1.min.js") ?>
<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("fix.container.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("growl-notification-bootstrap-alert/bootstrap-notify.min.js") ?>
<?= Html::js("notify.js") ?>
<?= Html::js("js-dropzone.js") ?>

<?= Alert::run() ?>

<?php include_once VIEWS . 'layouts/_modal.php'; ?>

<script>
    const url = "<?= Utils::urlTo('site/upload?p=' . Utils::fmEnc($p)) ?>";

    const dropzone = new Dropzone("div.my-dropzone", {
        url: url,
        chunking: true,
        chunkSize: 2000000,
        forceChunking: true,
        retryChunks: true,
        retryChunksLimit: 3,
        parallelUploads: 1,
        parallelChunkUploads: false,
        timeout: 120000,
        maxFilesize: 5000000000,
        acceptedFiles : "",
        autoProcessQueue: true,
        init: function () {
            const dropzoneInstance = this;

            document.body.addEventListener("dragover", function (e) {
                e.preventDefault();
                e.stopPropagation();
            });

            document.body.addEventListener("drop", function (e) {
                e.preventDefault();
                e.stopPropagation();
                const files = e.dataTransfer.files;
                if (files.length) {
                    Array.from(files).forEach(file => dropzoneInstance.addFile(file));
                }
            });

            dropzoneInstance.on("queuecomplete", function () {
                window.location.reload();
            });

            dropzoneInstance.on("sending", function (file, xhr, formData) {
                formData.set('fullpath', (file.fullPath) ? file.fullPath : file.name);
                xhr.ontimeout = (function() {
                    nerror('Error: Server Timeout');
                });
            });

            dropzoneInstance.on("success", function (res) {
                let _response = JSON.parse(res.xhr.response);

                if(_response.status === "error") {
                    nerror(_response.info);
                } else {
                    nsuccess(_response.info);
                }
            });

            dropzoneInstance.on("error", function(file, response) {
                nerror(response);
            });
        }
    });

</script>

</body>
</html>
