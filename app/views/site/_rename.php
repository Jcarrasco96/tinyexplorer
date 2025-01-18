<?php

/** @var string $action */
/** @var string $oldName */

?>

<form id="form-rename" class="g-3" action="<?= $action ?>" method="post">

    <div class="mb-3">
        <label for="inputNewName" class="form-label">New name</label>
        <input type="text" class="form-control" id="inputNewName" name="newName" required>
        <div id="invalid-name" class="invalid-feedback"></div>
    </div>

    <input type="hidden" value="<?= $oldName ?>" name="oldName">

    <button type="submit" class="btn btn-bd-primary"><i class="bi bi-plus-lg"></i> Submit</button>

</form>
