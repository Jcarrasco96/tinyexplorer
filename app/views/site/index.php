<?php

use app\core\Alert;
use app\core\App;
use app\core\Breadcrumb;
use app\core\Html;
use app\core\Utils;

/** @var ?string $p */
/** @var ?string $parent */
/** @var array $arrFolders */
/** @var array $arrFiles */

$time_start = microtime(true);

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

<main id="content" class="content" style="display: none;">

    <div class="container">

        <div class="d-flex align-items-center mb-2">
            <?php if ($parent !== false): ?>
                <nav aria-label="breadcrumb" class="me-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index?p=' .  base64_encode($parent)) ?>"><i class="bi bi-chevron-left"></i></a></li>
                    </ol>
                </nav>

                <?= Breadcrumb::run(['path' => $p]) ?>
            <?php else: ?>
                <nav aria-label="breadcrumb" class="me-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a></li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>

        <div class="row row-cols-1 g-1">
            <?php foreach ($arrFolders as $kFolder => $vFolder): ?>
                <div class="col">
                    <div class="card" id="card-folder" data-url="<?= Utils::urlTo('site/index?p=' . $vFolder['link']) ?>">
                        <div class="row card-body px-3 py-2 align-items-center">
                            <div class="col-auto" style="width: calc(100% - 410px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFolder['bi_icon'] ?>"></i> <?= Utils::fmConvertWin($vFolder['encFile']) ?></h5></div>
                            <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFolder['modification_date'] ?></div><!-- TODO arreglar d-lg- -->
                            <div style="width: 90px;"><small class="text-body-secondary me-auto">Folder</small></div>
                            <div class="text-end" style="width: 140px;">
                                <p class="mb-0">
                                    <a class="btn btn-bd-primary btn-sm" id="btn-rename" title="Rename" href="<?= Utils::urlTo('site/rename?p=' . Utils::fmEnc($p) . '&f=' . Utils::fmEnc($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($arrFiles as $kFile => $vFile): ?>
                <div class="col">
                    <div class="card" id="card-file" data-url="<?= Utils::urlTo('site/view?p=' . $vFile['link']) ?>">
                        <div class="row card-body px-3 py-2 align-items-center">
                            <div class="col-auto" style="width: calc(100% - 410px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFile['bi_icon'] ?>"></i> <?= Utils::fmConvertWin($vFile['encFile']) ?></h5></div>
                            <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFile['modification_date'] ?></div><!-- TODO arreglar d-lg- -->
                            <div style="width: 90px;"><small class="text-body-secondary me-auto"><?= $vFile['filesize'] ?></small></div>
                            <div class="text-end" style="width: 140px;">
                                <p class="mb-0">
                                    <a class="btn btn-bd-primary btn-sm" id="btn-rename" title="Rename" href="<?= Utils::urlTo('site/rename?p=' . Utils::fmEnc($p) . '&f=' . Utils::fmEnc($vFile['f'])) ?>" data-type="file"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                    <a class="btn btn-bd-primary btn-sm" id="btn-download" title="Download" href="<?= Utils::urlTo('site/download?p=' . Utils::fmEnc($p) . '&df=' . Utils::fmEnc($vFile['f'])) ?>"><i class="bi bi-download" aria-hidden="true"></i></a>
<!--                                    <a class="btn btn-bd-primary btn-sm" id="btn-download" title="Direct link" href="--><?php //= Utils::urlTo('site/download?&jwt=' . $vFile['directLink']) ?><!--"><i class="bi bi-link-45deg" aria-hidden="true"></i></a>-->
                                    <a class="btn btn-bd-primary btn-sm" id="btn-delete" title="Delete" href="<?= Utils::urlTo('site/delete?p=' . Utils::fmEnc($p) . '&f=' . Utils::fmEnc($vFile['f'])) ?>"><i class="bi bi-trash" aria-hidden="true"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($arrFolders) && empty($arrFiles)): ?>
                <div class="col">
                    <div class="card card-disabled">
                        <div class="card-body px-3 py-2">
                            <h5 class="mb-0 py-1"><i class="bi bi-folder-x"></i> <em>Folder is empty</em></h5>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="my-dropzone dropzone">
            <div class="dz-message text-body-tertiary">Drag your files here or click to upload.</div>
        </div>

        <div id="progress-container">
            <div id="progress-info" class="d-flex">
                <p class="mb-0 me-auto" id="progress-filename"></p>
                <p class="mb-0" id="progress-percent"></p>
            </div>
            <div class="progress" style="height: 5px">
                <div id="progress-bar" class="progress-bar" style="width: 50%"></div>
            </div>
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
<?= Html::js("dropzone-min.js") ?>

<?= Alert::run() ?>

<?php include_once VIEWS . 'layouts/_modal.php'; ?>

<style>
    .content.active {
        opacity: 0.4;
    }
</style>

<script>

    document.addEventListener("DOMContentLoaded", () => {
        const dropArea = document.getElementById("content");

        const preventDefaults = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            document.addEventListener(eventName, preventDefaults, false);
        });

        document.addEventListener('dragenter', () => {
            dropArea.classList.add('active');
        });

        document.addEventListener('dragleave', (e) => {
            if (!e.relatedTarget || e.relatedTarget === document.documentElement) {
                dropArea.classList.remove('active');
            }
        });

        document.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            dropArea.classList.remove('active');

            if (files.length) {
                for (const file of files) {
                    console.log(`Name: ${file.name}, Size: ${file.size}`);
                }
            }
        });
    });

    $(document).on("click", "#btn-download", function (event) {
        event.preventDefault();

        let link = document.createElement('a');
        link.href = $(this).attr('href');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        return false;
    });

    $(document).on("click", "#card-folder, #card-file", function (event) {
        event.preventDefault();

        let url = $(this).data('url');

        window.location.assign(url);

        return false;
    });

    const url = "<?= Utils::urlTo('site/upload?p=' . Utils::fmEnc($p)) ?>";

    function updateProgressBar(progress, fileName) {
        const container = document.getElementById('progress-container');
        const bar = document.getElementById('progress-bar');
        const filename = document.getElementById('progress-filename');
        const percent = document.getElementById('progress-percent');

        container.style.display = 'block';
        bar.style.width = progress + '%';

        filename.textContent = `${fileName}`;
        percent.textContent = `${Math.round(progress)}%`;
    }

    // const dropzone = new Dropzone("div.my-dropzone", {
    //     url: url,
    //     chunking: true,
    //     chunkSize: 2000000,
    //     forceChunking: true,
    //     retryChunks: true,
    //     retryChunksLimit: 3,
    //     parallelUploads: 1,
    //     parallelChunkUploads: false,
    //     timeout: 120000,
    //     maxFilesize: 5000000000,
    //     acceptedFiles : "",
    //     autoProcessQueue: true,
    //     init: function () {
    //         const dropzoneInstance = this;
    //
    //         document.body.addEventListener("dragover", function (e) {
    //             e.preventDefault();
    //             e.stopPropagation();
    //         });
    //
    //         document.body.addEventListener("drop", function (e) {
    //             e.preventDefault();
    //             e.stopPropagation();
    //             const files = e.dataTransfer.files;
    //             if (files.length) {
    //                 Array.from(files).forEach(file => dropzoneInstance.addFile(file));
    //             }
    //         });
    //
    //         dropzoneInstance.on("uploadprogress", function (file, progress) {
    //             updateProgressBar(progress, file.name);
    //         });
    //
    //         dropzoneInstance.on("complete", function (file) {
    //             hideProgressBar();
    //         });
    //
    //         dropzoneInstance.on("queuecomplete", function () {
    //             window.location.reload();
    //         });
    //
    //         dropzoneInstance.on("sending", function (file, xhr, formData) {
    //             formData.set('fullpath', (file.fullPath) ? file.fullPath : file.name);
    //             xhr.ontimeout = (function() {
    //                 nerror('Error: Server Timeout');
    //             });
    //         });
    //
    //         dropzoneInstance.on("success", function (res) {
    //             let _response = JSON.parse(res.xhr.response);
    //
    //             if(_response.status === "error") {
    //                 nerror(_response.info);
    //             } else {
    //                 nsuccess(_response.info);
    //             }
    //         });
    //
    //         dropzoneInstance.on("error", function(file, response) {
    //             nerror(response);
    //         });
    //     }
    // });

    function hideProgressBar() {
        const container = document.getElementById('progress-container');
        const bar = document.getElementById('progress-bar');
        const filename = document.getElementById('progress-filename');
        const percent = document.getElementById('progress-percent');

        container.style.display = 'none';
        bar.style.width = '0%';
        filename.textContent = '';
        percent.textContent = '';
    }

    $(document).on("click", "#btn-rename", function (event) {
        event.preventDefault();

        let title = $(this).data('type') === 'file' ? 'Rename file' : 'Rename folder';
        let url = $(this).attr('href') + '&t=' + $(this).data('type');

        $("#modal-app-title").html(title);

        $.ajax({
            type: 'get',
            url: url
        }).done(function (response) {
            $("#modal-app-container").html(response);
            $("#modal-app").modal('show');
        });

        return false;
    });

    $(document).on('submit', "#form-rename", function (event) {
        event.preventDefault();

        let buttonSubmit = $(this).find(":submit");
        let content = buttonSubmit.html();

        buttonSubmit.html("<i class='bi-hourglass'></i> Loading...");

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serializeArray(),
            success: function(data) {
                /** @var {number} data.status */
                /** @var {array} data.error */
                /** @var {string} data.message */

                $('#invalid-name').html(data.error.name || '');

                if (data.error.name) {
                    $('#inputName').removeClass('is-valid').addClass('is-invalid');
                } else {
                    $('#inputName').removeClass('is-invalid').addClass('is-valid');
                }

                buttonSubmit.html(content);

                if (data.status === 200) {
                    $("#modal-app").modal('hide');
                    window.location.reload();
                }
            },
            error: function(jqXHR, textStatus) {
                nerror(textStatus);
            }
        });

        return false;
    });
</script>

</body>
</html>