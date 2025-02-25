<?php

use TE\core\App;
use TE\helpers\Utils;

?>

<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-2">
        <p class="display-6 mb-0">Settings</p>

        <p class="ms-2 mb-0"></p>
    </div>

    <hr>

    <div class="row row-cols-1 g-1">
        <div class="col">
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
                    <label for="selectLanguage" class="col-sm-2 col-form-label"><?= App::t('Language') ?></label>
                    <div class="col-sm-6 col-lg-4">
                        <select class="form-select" id="selectLanguage" name="language">
                            <option value="en" <?= App::$language->isCode('en') ? 'selected' : '' ?>><?= App::t('English') ?></option>
                            <option value="es" <?= App::$language->isCode('es') ? 'selected' : '' ?>><?= App::t('Spanish') ?></option>
                        </select>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <label for="inputRootPath" class="col-sm-2 col-form-label"><?= App::t('Root path') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputRootPath" name="rootPath" value="<?= App::$system->rootPath ?>" required>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <label for="selectUseCurl" class="col-sm-2 col-form-label"><?= App::t('Use CURL') ?></label>
                    <div class="col-sm-10 col-lg-4">
                        <select class="form-select" id="selectUseCurl" name="useCurl" aria-describedby="selectCurlHelp">
                            <option value="y" <?= App::$system->isCurl() ? 'selected' : '' ?>><?= App::t('Yes') ?></option>
                            <option value="n" <?= !App::$system->isCurl() ? 'selected' : '' ?>><?= App::t('No') ?></option>
                        </select>
                        <div id="selectCurlHelp" class="form-text">Use CURL to download/upload files.</div>
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
    </div>

</div>