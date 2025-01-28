<?php

use app\core\App;
use app\utils\Utils;

?>

<form id="form-new_user" method="post" action="<?= Utils::urlTo('admin/users/new') ?>">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">

    <div class="mb-3">
        <label for="inputUsername" class="form-label"><?= App::t('Username') ?></label>
        <input type="email" class="form-control" id="inputUsername" name="username" value="" required>
        <div id="invalid-username" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="inputPassword" class="form-label"><?= App::t('Password') ?></label>
        <input type="password" class="form-control" id="inputPassword" name="password" value="" required>
        <div id="invalid-password" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-plus-lg"></i> <?= App::t('New user') ?></button>
    </div>

</form>