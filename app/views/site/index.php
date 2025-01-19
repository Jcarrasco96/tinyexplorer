<?php

use app\core\App;
use app\helpers\Breadcrumb;
use app\services\FileSystem;
use app\utils\Utils;

/** @var ?string $p */
/** @var ?string $parent */
/** @var array $arrFolders */
/** @var array $arrFiles */

$time_start = microtime(true);

?>

<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <?php if ($parent !== false): ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index?p=' .  base64_encode($parent)) ?>"><i class="bi bi-chevron-left"></i></a>

            <?= Breadcrumb::run(['path' => $p]) ?>
        <?php else: ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a>
        <?php endif; ?>

        <p class="ms-2 mb-0">
            <button type="button" class="btn btn-bd-primary" id="btn-new" data-url="<?= Utils::urlTo('site/new-ajax?p=' . base64_encode($p)) ?>" data-type="file"><i class="bi bi-file-plus"></i> <?= App::t('New file') ?></button>
            <button type="button" class="btn btn-bd-primary" id="btn-new" data-url="<?= Utils::urlTo('site/new-ajax?p=' . base64_encode($p)) ?>" data-type="folder"><i class="bi bi-folder-plus"></i> <?= App::t('New folder') ?></button>
            <a class="btn btn-bd-primary" href="<?= Utils::urlTo('site/upload?p=' . base64_encode($p)) ?>"><i class="bi bi-upload"></i> <?= App::t('Upload') ?></a>
        </p>
    </div>

    <div class="row row-cols-1 g-1">
        <?php foreach ($arrFolders as $kFolder => $vFolder): ?>
            <div class="card card-selection" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/index?p=' . $vFolder['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 450px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFolder['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFolder['encFile']) ?></h5></div>
                    <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFolder['modification_date'] ?></div>
                    <div style="width: 100px;"><small class="text-body-secondary me-auto"><?= App::t('Folder') ?></small></div>
                    <div class="text-end" style="width: 170px;">
                        <p class="mb-0">
                            <button class="btn btn-bd-primary btn-sm" id="btn-compress-zip" title="<?= App::t('Compress to ZIP') ?>" data-url="<?= Utils::urlTo('site/compress?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>"><i class="bi bi-file-earmark-zip" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-rename" title="<?= App::t('Rename') ?>" data-url="<?= Utils::urlTo('site/rename?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-input-cursor-text" aria-hidden="true"></i></button>
                            <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/delete?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($arrFiles as $kFile => $vFile): ?>
            <div class="card card-selection" data-card-id="card-file-<?= $kFile ?>" data-url="<?= Utils::urlTo('site/view?p=' . $vFile['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 450px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFile['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFile['encFile']) ?></h5></div>
                    <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFile['modification_date'] ?></div>
                    <div style="width: 100px;"><small class="text-body-secondary me-auto"><?= $vFile['filesize'] ?></small></div>
                    <div class="text-end" style="width: 170px;">
                        <p class="mb-0">
                            <button class="btn btn-bd-primary btn-sm" id="btn-share" title="<?= App::t('Share') ?>" data-url="<?= Utils::urlTo('site/share?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>"><i class="bi bi-share" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-download" title="<?= App::t('Download') ?>" data-url="<?= Utils::urlTo('site/download?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>"><i class="bi bi-download" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-rename" title="<?= App::t('Rename') ?>" data-url="<?= Utils::urlTo('site/rename?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>" data-type="file"><i class="bi bi-input-cursor-text" aria-hidden="true"></i></button>
                            <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-card-id="card-file-<?= $kFile ?>" data-url="<?= Utils::urlTo('site/delete?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>" data-type="file"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </p>
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
        <div class="dz-message text-body-tertiary"><?= App::t('Drag your files here or click to upload.') ?></div>
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

<style>
    .content.active {
        opacity: 0.4;
    }
</style>

<script>

    const url = "<?= Utils::urlTo('api/upload?p=' . base64_encode($p)) ?>";

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

            dropzoneInstance.on("uploadprogress", function (file, progress) {
                updateProgressBar(progress, file.name);
            });

            dropzoneInstance.on("complete", function (file) {
                hideProgressBar();
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

    // document.addEventListener("DOMContentLoaded", () => {
    //     const dropArea = document.getElementById("content");
    //
    //     const preventDefaults = (e) => {
    //         e.preventDefault();
    //         e.stopPropagation();
    //     };
    //
    //     ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    //         document.addEventListener(eventName, preventDefaults, false);
    //     });
    //
    //     document.addEventListener('dragenter', () => {
    //         dropArea.classList.add('active');
    //     });
    //
    //     document.addEventListener('dragleave', (e) => {
    //         if (!e.relatedTarget || e.relatedTarget === document.documentElement) {
    //             dropArea.classList.remove('active');
    //         }
    //     });
    //
    //     document.addEventListener('drop', (e) => {
    //         const files = e.dataTransfer.files;
    //         dropArea.classList.remove('active');
    //
    //         if (files.length) {
    //             for (const file of files) {
    //                 console.log(`Name: ${file.name}, Size: ${file.size}`);
    //             }
    //         }
    //     });
    // });

    $('#modal-app').on('hidden.bs.modal', () => {
        $("#modal-app-container").empty();
        $("#modal-app-title").empty();
    });

    $(document).on("click", "#btn-download, #btn-compress-zip", function (event) {
        event.preventDefault();

        let link = document.createElement('a');
        link.href = $(this).data('url');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        return false;
    });

    $(document).on("click", "#btn-new, #btn-rename, #btn-delete, #btn-share", function (event) {
        event.preventDefault();

        let title = '';
        let url = '';

        switch ($(this).attr('id')) {
            case 'btn-new':
                title = 'New ' + $(this).data('type');
                url = $(this).data('url') + '&t=' + $(this).data('type');
                break;

            case 'btn-rename':
                title = 'Rename ' + $(this).data('type');
                url = $(this).data('url') + '&t=' + $(this).data('type');
                break;

            case 'btn-delete':
                title = 'Delete ' + $(this).data('type') + '?';
                url = $(this).data('url') + '&t=' + $(this).data('type') + '&cardId=' + $(this).data('card-id');
                break;

            case 'btn-share':
                title = 'Share file';
                url = $(this).data('url');
                break;
        }

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

    $(document).on("click", ".card-selection", function (event) {
        event.preventDefault();
        let url = $(this).data('url');
        window.location.assign(url);
        return false;
    });

    $(document).on('submit', "#form-new, #form-rename", function (event) {
        event.preventDefault();

        let isFormRename = $(this).attr('id') === 'form-rename';

        let buttonSubmit = $(this).find(":submit");
        let content = buttonSubmit.html();

        buttonSubmit.html("<i class='bi-hourglass'></i> Loading...");

        $('.btn-close').addClass('disabled');

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            dataType: 'json',
            data: $(this).serializeArray(),
            success: function(data) {
                /** @var {string} data.status */

                showErrorName(data, isFormRename);

                if (data.status === 'success') {
                    $("#modal-app").modal('hide');
                    window.location.reload();
                }
            },
            error: function(jqXHR, textStatus) {
                /** @var {array} jqXHR.responseJSON */
                /** @var {array} jqXHR.responseJSON.error */
                /** @var {string} jqXHR.responseJSON.message */

                if (jqXHR.responseJSON.error && jqXHR.responseJSON.error.hasOwnProperty('newName')) {
                    showErrorName(jqXHR.responseJSON, isFormRename);
                } else {
                    nerror(jqXHR.responseJSON.message);
                }
            },
            complete: function () {
                buttonSubmit.html(content);
                $('.btn-close').removeClass('disabled');
            }
        });

        return false;
    });

    $(document).on('submit', "#form-delete", function (event) {
        event.preventDefault();

        let buttonSubmit = $(this).find(":submit");
        let buttonCancel = $(this).find(":button");
        let content = buttonSubmit.html();

        buttonSubmit.html("<i class='bi-hourglass'></i> Loading...");

        $('.btn-close').addClass('disabled');
        buttonCancel.addClass('disabled');

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            dataType: 'json',
            data: $(this).serializeArray(),
            success: function(data) {
                /** @var {string} data.status */
                if (data.status === 'success') {
                    $("#modal-app").modal('hide');
                    $(`.card[data-card-id="${data.data.cardId}"]`).remove();
                    nsuccess(data.message);
                }
            },
            error: function(jqXHR, textStatus) {
                nerror(jqXHR.responseJSON.message);
            },
            complete: function () {
                buttonSubmit.html(content);
                $('.btn-close').removeClass('disabled');
                buttonCancel.removeClass('disabled');
            }
        });

        return false;
    });

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

    function showErrorName(data, isFormRename) {
        /** @var {string} data.status */
        /** @var {array} data.error */
        /** @var {string} data.message */

        if (isFormRename) {
            $('#invalid-new-name').html(data.error.newName || '');

            if (data.error.newName) {
                $('#inputNewName').removeClass('is-valid').addClass('is-invalid');
            } else {
                $('#inputNewName').removeClass('is-invalid').addClass('is-valid');
            }
        } else {
            $('#invalid-name').html(data.error.name || '');

            if (data.error.name) {
                $('#inputName').removeClass('is-valid').addClass('is-invalid');
            } else {
                $('#inputName').removeClass('is-invalid').addClass('is-valid');
            }
        }
    }

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

</script>
