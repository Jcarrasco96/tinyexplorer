<?php

/** @var string $action */
/** @var string $type */

use app\core\App;

?>

<form id="form-new" class="g-3" action="<?= $action ?>" method="post">

    <div class="mb-3">
        <label for="inputName" class="form-label"><?= App::t('Name for the new {type}', [App::t($type)]) ?></label>
        <input type="text" class="form-control" id="inputName" name="name" required>
        <div id="invalid-name" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-plus-lg"></i> <?= App::t('New {type}', [App::t($type)]) ?></button>
    </div>

</form>
