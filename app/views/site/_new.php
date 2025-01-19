<?php

/** @var string $action */
/** @var string $type */

?>

<form id="form-new" class="g-3" action="<?= $action ?>" method="post">

    <div class="mb-3">
        <label for="inputName" class="form-label">Name for the new <?= $type ?></label>
        <input type="text" class="form-control" id="inputName" name="name" required>
        <div id="invalid-name" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-plus-lg"></i> New <?= $type ?></button>
    </div>

</form>
