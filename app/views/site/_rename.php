<?php

/** @var string $file */

use app\core\App;
use app\utils\Utils;

$oldName = base64_decode($file);

?>

<form id="form-rename" action="<?= Utils::urlTo('site/rename/' . $file) ?>" method="post">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">

    <div class="mb-3">
        <label for="inputNewName" class="form-label"><?= App::t('New name for <code>{oldName}</code>', [$oldName]) ?></label>
        <input type="text" class="form-control" id="inputNewName" name="newName" required placeholder="<?= $oldName ?>" value="<?= $oldName ?>">
        <div id="invalid-new-name" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-input-cursor-text"></i> <?= App::t('Rename') ?></button>
    </div>

</form>
