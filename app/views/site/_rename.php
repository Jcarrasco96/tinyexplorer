<?php

/** @var string $action */
/** @var string $oldName */

?>

<form id="form-rename" class="g-3" action="<?= $action ?>" method="post">

    <div class="mb-3">
        <label for="inputNewName" class="form-label">New name for <code><?= $oldName ?></code></label>
        <input type="text" class="form-control" id="inputNewName" name="newName" required placeholder="<?= $oldName ?>">
        <div id="invalid-new-name" class="invalid-feedback"></div>
    </div>

    <div class="d-flex">
        <button type="submit" class="btn btn-bd-primary ms-auto"><i class="bi bi-input-cursor-text"></i> Rename</button>
    </div>

</form>
