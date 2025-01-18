<?php

/** @var string $action */

?>

<form id="form-new" class="g-3" action="<?= $action ?>" method="post">

    <div class="mb-3">
        <label for="inputName" class="form-label">Name</label>
        <input type="text" class="form-control" id="inputName" name="name" required>
        <div id="invalid-name" class="invalid-feedback"></div>
    </div>

    <button type="submit" class="btn btn-bd-primary"><i class="bi bi-plus-lg"></i> Submit</button>

</form>
