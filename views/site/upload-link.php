<?php

use TE\helpers\Breadcrumb;
use TE\helpers\Utils;

/** @var ?string $p */

?>

<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <?php if (!empty($p)): ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index/' .  base64_encode($p)) ?>"><i class="bi bi-chevron-left"></i></a>
            <?= Breadcrumb::run(['path' => $p]) ?>
        <?php else: ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" id="form-upload" action="<?= Utils::urlTo('api/upload-link/' . base64_encode($p)) ?>">
                <div class="mb-3">
                    <label for="inputLink" class="form-label">Direct link</label>
                    <input type="url" class="form-control" id="inputLink" name="directLink" required placeholder="https://">
                </div>
                <button type="submit" class="btn btn-bd-primary"><i class="bi bi-upload"></i> Upload</button>
            </form>
        </div>
    </div>

</div>

<script>

    $(document).on('submit', "#form-upload", function (event) {
        event.preventDefault();

        let buttonSubmit = $(this).find(":submit");
        let content = buttonSubmit.html();

        buttonSubmit.html("<i class='bi-hourglass'></i> Uploading...");
        buttonSubmit.addClass('disabled');

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            dataType: 'json',
            data: $(this).serializeArray(),
            success: function(data) {
                /** @var {string} data.status */
                if (data.status === 'success') {
                    nsuccess(data.message);
                }
            },
            error: function(jqXHR, textStatus) {
                buttonSubmit.html(content);
                buttonSubmit.removeClass('disabled');

                /** @var {array} jqXHR.responseJSON */
                /** @var {array} jqXHR.responseJSON.error */
                /** @var {string} jqXHR.responseJSON.message */
                nerror(textStatus);
            },
            complete: function () {
                buttonSubmit.html(content);
                buttonSubmit.removeClass('disabled');
            }
        });

        return false;
    });

</script>
