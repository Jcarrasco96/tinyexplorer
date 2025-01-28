<?php

use app\core\App;
use app\utils\Utils;

/** @var bool $isMe */
/** @var int $id */

?>

<form id="form-change_password" method="post" action="<?= Utils::urlTo('admin/users/' . $id . '/change-password/') ?>">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">

    <?php if ($isMe): ?>
    <div class="mb-3">
        <label for="inputOldPassword" class="form-label"><?= App::t('Old Password') ?></label>
        <input type="password" class="form-control" id="inputOldPassword" name="old_password" value="" required>
        <div id="invalid-old_password" class="invalid-feedback"></div>
    </div>

    <hr>
    <?php endif; ?>

    <div class="mb-3">
        <label for="inputNewPassword" class="form-label"><?= App::t('New Password') ?></label>
        <input type="password" class="form-control" id="inputNewPassword" name="new_password" value="" required>
        <div id="invalid-new_password" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="inputRePassword" class="form-label"><?= App::t('Retype Password') ?></label>
        <input type="password" class="form-control" id="inputRePassword" name="re_password" value="" required>
        <div id="invalid-re_password" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-key"></i> <?= App::t('Change password') ?></button>
    </div>

</form>