<?php

/** @var string $p */
/** @var string $f */
/** @var array $arrFolders */
/** @var string $type */
/** @var string $file */
/** @var ?string $parent */

/** @var Renderer $renderer */

use app\core\App;
use app\core\Renderer;
use app\helpers\BreadcrumbCopy;
use app\utils\Utils;

?>

<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <?php if ($parent !== false): ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/copy/' .  $type . '/' . base64_encode($f) . '?p=' . base64_encode($parent)) ?>"><i class="bi bi-chevron-left"></i></a>
            <?= BreadcrumbCopy::run(['path' => $p, 'file' => $f, 'type' => $type]) ?>
        <?php else: ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a>
        <?php endif; ?>

        <p class="ms-2 mb-0">
            <button type="button" class="btn btn-bd-primary" id="btn-new" data-url="<?= Utils::urlTo('site/new-ajax/folder/') ?>"><i class="bi bi-folder-plus"></i> <?= App::t('New folder') ?></button>

            <button type="button" class="btn btn-bd-primary" id="btn-copy" data-p="<?= base64_encode($p) ?>" data-f="<?= base64_encode($f) ?>" data-t="<?= $type ?>"><i class="bi bi-copy"></i> Copy here...</button>
            <button type="button" class="btn btn-bd-primary" id="btn-move" data-p="<?= base64_encode($p) ?>" data-f="<?= base64_encode($f) ?>" data-t="<?= $type ?>"><i class="bi bi-scissors"></i> Move here...</button>
        </p>
    </div>

    <div class="row row-cols-1 g-1">
        <div class="col">
            <div class="card card-disabled">
                <div class="card-body px-3 py-2">
                    <h5 class="mb-0 py-1"><?= $type == 'file' ? '<i class="bi bi-file-earmark"></i>' : '<i class="bi bi-folder"></i>' ?> <em><?= $type == 'file' ? 'File' : 'Folder' ?> selected: <code><?= basename($file) ?></code></em></h5>
                </div>
            </div>
        </div>

        <?= $renderer->renderPartial('_list_folders', [
            'controllerName' => 'site',
            'p' => $p,
            'f' => $f,
            'file' => $file,
            'arrFolders' => $arrFolders,
            'type' => $type,
            'isCopy' => true,
        ]) ?>

        <?php if (empty($arrFolders)): ?>
            <div class="col">
                <div class="card card-disabled">
                    <div class="card-body px-3 py-2">
                        <h5 class="mb-0 py-1"><i class="bi bi-folder-x"></i> <em>Folder is empty</em></h5>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    $(document).on("click", ".card-selection", function (event) {
        event.preventDefault();
        let url = $(this).data('url');
        window.location.assign(url);
        return false;
    });

    $(document).on("click", "#btn-new", function (event) {
        event.preventDefault();

        $("#modal-app-title").html('New folder');

        $.ajax({
            type: 'get',
            url: $(this).data('url')
        }).done(function (response) {
            $("#modal-app-container").html(response);
            $("#modal-app").modal('show');
        });

        return false;
    });

    $(document).on('submit', "#form-new", function (event) {
        event.preventDefault();

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

                showErrorName(data);

                if (data.status === 'success') {
                    $("#modal-app").modal('hide');
                    window.location.reload();
                }
            },
            error: function(jqXHR, textStatus) {
                /** @var {array} jqXHR.responseJSON */
                /** @var {array} jqXHR.responseJSON.error */
                /** @var {string} jqXHR.responseJSON.message */

                if (jqXHR.responseJSON.error && jqXHR.responseJSON.error.hasOwnProperty('name')) {
                    showErrorName(jqXHR.responseJSON);
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

    function showErrorName(data) {
        /** @var {string} data.status */
        /** @var {array} data.error */
        /** @var {string} data.message */

        $('#invalid-name').html(data.error.name || '');

        if (data.error.name) {
            $('#inputName').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputName').removeClass('is-invalid').addClass('is-valid');
        }
    }

    $(document).on("click", "#btn-copy, #btn-move", function (event) {
        event.preventDefault();

        let button = $(this);
        let content = button.html();

        let btnText = $(this).attr('id') === 'btn-copy' ? 'Copying...' : 'Moving...';

        button.html("<i class='bi-hourglass'></i> " + btnText);
        button.addClass('disabled');

        const formData = new FormData();
        formData.append('p', $(this).data('p'));
        formData.append('f', $(this).data('f'));
        formData.append('copy', $(this).attr('id') === 'btn-copy' ? 'copy' : 'move');
        formData.append('t', $(this).data('t'));

        $.ajax({
            url: "<?= Utils::urlTo('api/copy') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                /** @var {string} data.status */
                window.location.assign("<?= Utils::urlTo('site/index?p=' . base64_encode($p)) ?>");
            },
            error: function(jqXHR, textStatus) {
                nerror(jqXHR.responseJSON.message);
            },
            complete: function () {
                button.html(content);
                button.removeClass('disabled');
            }
        });

        return false;
    });
</script>