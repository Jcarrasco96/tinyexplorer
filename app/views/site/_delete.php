<?php

/** @var string $action */
/** @var string $type */
/** @var string $p */
/** @var string $f */
/** @var string $cardId */

use app\core\App;

?>

<form id="form-delete" class="g-3" action="<?= $action ?>" method="post">

    <input type="hidden" name="p" value="<?= base64_encode($p) ?>">
    <input type="hidden" name="f" value="<?= base64_encode($f) ?>">
    <input type="hidden" name="cardId" value="<?= $cardId ?>">

    <h2 class="fs-5 mb-3"><?= App::t('Are you sure you want to delete the {type} <code>{filename}</code>?', [App::t($type), $f]) ?></h2>

    <div class="d-flex">
        <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> <?= App::t('No, cancel') ?></button>
        <button type="submit" class="btn btn-danger ms-2"><i class="bi bi-trash"></i> <?= App::t('Yes, delete') ?></button>
    </div>

</form>
