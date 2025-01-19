<?php

use app\core\App;
use app\utils\Utils;

?>

<div class="container">

    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">

            <form method="post" action="<?= Utils::urlTo('site/settings') ?>">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">

                <div class="row mb-3">
                    <label for="selectTheme" class="col-sm-2 col-form-label"><?= App::t('Theme') ?></label>
                    <div class="col-sm-6 col-lg-4">
                        <select class="form-select" id="selectTheme" name="theme">
                            <option value="dark" <?= !App::$system->isLightTheme() ? 'selected' : '' ?>><?= App::t('Dark') ?></option>
                            <option value="light" <?= App::$system->isLightTheme() ? 'selected' : '' ?>><?= App::t('Light') ?></option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="inputRootPath" class="col-sm-2 col-form-label"><?= App::t('Root path') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputRootPath" name="rootPath" value="<?= App::$system->rootPath ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="selectLanguage" class="col-sm-2 col-form-label"><?= App::t('Language') ?></label>
                    <div class="col-sm-6 col-lg-4">
                        <select class="form-select" id="selectLanguage" name="language">
                            <option value="en" <?= App::$lng->lngCode == 'en' ? 'selected' : '' ?>><?= App::t('English') ?></option>
                            <option value="es" <?= App::$lng->lngCode == 'es' ? 'selected' : '' ?>><?= App::t('Spanish') ?></option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10">
                        <button type="submit" class="btn btn-bd-primary"><i class="bi bi-save"></i> <?= App::t('Save settings') ?></button>
                    </div>
                </div>

            </form>

        </div>
        <div class="col-md-3"></div>
    </div>

</div>