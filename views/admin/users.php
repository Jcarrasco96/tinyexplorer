<?php

/** @var array $users */

use TE\core\App;
use TE\helpers\Utils;

$rowNum = 1;

?>

<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <p class="display-6 mb-0">Users</p>

        <p class="ms-2 mb-0">
            <button type="button" class="btn btn-bd-primary" id="btn-change_my_password" data-url="<?= Utils::urlTo('admin/users/' . App::$session->_id() . '/change-password') ?>"><i class="bi bi-key"></i> <?= App::t('Change my password') ?></button>
            <button type="button" class="btn btn-bd-primary" id="btn-new_user" data-url="<?= Utils::urlTo('admin/users/new') ?>"><i class="bi bi-person-add"></i> <?= App::t('New user') ?></button>
        </p>
    </div>

    <hr>

    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Username</th>
            <th scope="col" class="text-end">Permissions</th>
            <th scope="col" class="text-end">Options</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <th class="align-middle" scope="row"><?= $rowNum++ ?></th>
                <td class="align-middle"><?= $user['username'] ?></td>
                <td class="align-middle text-end">
                    <?php if (App::$session->_id() != $user['id']): ?>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic1_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cDelete" <?= $user['info']['cDelete'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic1_<?= $user['id'] ?>">Delete</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic2_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cUpload" <?= $user['info']['cUpload'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic2_<?= $user['id'] ?>">Upload</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic3_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cRename" <?= $user['info']['cRename'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic3_<?= $user['id'] ?>">Rename</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic4_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cCopy" <?= $user['info']['cCopy'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic4_<?= $user['id'] ?>">Copy/Move</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic5_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cCompress" <?= $user['info']['cCompress'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic5_<?= $user['id'] ?>">Compress</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic6_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cDownload" <?= $user['info']['cDownload'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic6_<?= $user['id'] ?>">Download</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic7_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cShare" <?= $user['info']['cShare'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic7_<?= $user['id'] ?>">Share</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="ic8_<?= $user['id'] ?>" data-id="<?= $user['id'] ?>" data-attribute="cAdmin" <?= $user['info']['cAdmin'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ic8_<?= $user['id'] ?>">Administrator</label>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-end" style="width: 90px;">
                    <?php if (App::$session->_id() != $user['id']): ?>
                        <button class="btn btn-bd-primary btn-sm" id="btn-change_password" title="<?= App::t('Change password') ?>" data-url="<?= Utils::urlTo('admin/users/' . $user['id'] . '/change-password') ?>"><i class="bi bi-key" aria-hidden="true"></i></button>
                        <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-url="<?= Utils::urlTo('admin/users/' . $user['id'] . '/delete') ?>"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script>

    $("input[type='checkbox']").on("click", function (event) {
        let element = $(this);
        let _id = element.data('id');
        let _attribute = element.data('attribute');

        let _isChecked = element.prop("checked");

        console.log(_isChecked);

        $.ajax({
            url: '<?= Utils::urlTo('admin/users/') ?>' + _id + '/' + _attribute,
            type: 'POST',
            dataType: 'json',
            data: $(this).serializeArray(),
            success: function(data) {
                element.prop('checked', _isChecked);
                nsuccess(data.message);
            },
            error: function(jqXHR, textStatus) {
                element.prop('checked', !_isChecked);
                nerror(jqXHR.responseJSON.message);
            }
        });

        return false;
    })

    $(document).on("click", "#btn-new_user, #btn-change_password, #btn-change_my_password, #btn-delete", function (event) {
        event.preventDefault();

        let title = '';

        switch ($(this).attr('id')) {
            case 'btn-new_user':
                title = '<i class="bi bi-person-add"></i> New user';
                break;

            case 'btn-change_password':
                title = '<i class="bi bi-key"></i> Change password';
                break;

            case 'btn-change_my_password':
                title = '<i class="bi bi-key"></i> Change my password';
                break;

            case 'btn-delete':
                title = '<i class="bi bi-trash"></i> Delete user?';
                break;
        }

        $("#modal-app-title").html(title);

        $.ajax({
            type: 'get',
            url: $(this).data('url')
        }).done(function (response) {
            $("#modal-app-container").html(response);
            $("#modal-app").modal('show');
        });

        return false;
    });

    $(document).on('submit', "#form-new_user", function (event) {
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

                if (jqXHR.responseJSON.error && jqXHR.responseJSON.error.hasOwnProperty('username')) {
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
                    window.location.reload();
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

    $(document).on('submit', "#form-change_password", function (event) {
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

                showErrorPassword(data);

                if (data.status === 'success') {
                    $("#modal-app").modal('hide');
                    window.location.reload();
                }
            },
            error: function(jqXHR, textStatus) {
                /** @var {array} jqXHR.responseJSON */
                /** @var {array} j.responseJSON.error */
                /** @var {string} jqXHR.responseJSON.message */

                if (jqXHR.responseJSON.error && (jqXHR.responseJSON.error.hasOwnProperty('old_password') || jqXHR.responseJSON.error.hasOwnProperty('new_password') || jqXHR.responseJSON.error.hasOwnProperty('re_password'))) {
                    showErrorPassword(jqXHR.responseJSON);
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

        $('#invalid-username').html(data.error.username || '');
        $('#invalid-password').html(data.error.password || '');

        if (data.error.username) {
            $('#inputUsername').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputUsername').removeClass('is-invalid').addClass('is-valid');
        }
        if (data.error.password) {
            $('#inputPassword').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputPassword').removeClass('is-invalid').addClass('is-valid');
        }
    }

    function showErrorPassword(data) {
        /** @var {string} data.status */
        /** @var {array} data.error */
        /** @var {string} data.message */

        $('#invalid-old_password').html(data.error.old_password || '');
        $('#invalid-new_password').html(data.error.new_password || '');
        $('#invalid-re_password').html(data.error.re_password || '');

        if (data.error.old_password) {
            $('#inputOldPassword').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputOldPassword').removeClass('is-invalid').addClass('is-valid');
        }
        if (data.error.new_password) {
            $('#inputNewPassword').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputNewPassword').removeClass('is-invalid').addClass('is-valid');
        }
        if (data.error.re_password) {
            $('#inputRePassword').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#inputRePassword').removeClass('is-invalid').addClass('is-valid');
        }
    }

</script>
