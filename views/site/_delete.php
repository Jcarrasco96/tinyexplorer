<?php

use TE\core\App;
use TE\helpers\Utils;

/** @var string $type */
/** @var string $f */
/** @var string $cardId */

?>

<form id="form-delete" action="<?= Utils::urlTo('api/delete') ?>" method="post">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">
    <input type="hidden" name="f" value="<?= base64_encode($f) ?>">
    <input type="hidden" name="cardId" value="<?= $cardId ?>">

    <h2 class="fs-5 mb-3"><?= App::t('Are you sure you want to delete the {type} <code>{filename}</code>?', [App::t($type), $f]) ?></h2>

    <div class="d-flex">
        <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> <?= App::t('No, cancel') ?></button>
        <button type="submit" class="btn btn-danger ms-2"><i class="bi bi-trash"></i> <?= App::t('Yes, delete') ?></button>
    </div>

</form>
