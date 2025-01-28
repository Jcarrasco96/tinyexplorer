<?php

use app\core\App;
use app\services\FileSystem;

$diskUsage = FileSystem::diskUsage(App::$system->rootPath);

?>

<div class="container">
    <footer class="d-flex flex-wrap justify-content-between align-items-end pt-2 my-2 border-top">
        <div class="col-12 col-md-8 d-flex align-items-center">
                <span class="mb-3 mb-md-0 text-body-secondary">
                    <?= App::$config['name'] ?> v<?= App::$config['version'] ?> © 2025 JC IT NETWORK, LLC
                    <br>
                    <?= App::t('This software is licensed under the {link}.', ['<a href="https://github.com/Jcarrasco96/tinyexplorer/blob/master/LICENSE">AGPL-3.0</a>']) ?> <?= App::t('Source code is available {link}.', ['<a href="https://github.com/Jcarrasco96/tinyexplorer" target="_blank">' . App::t('here') . '</a>']) ?>
                </span>
        </div>
        <ul class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-end list-unstyled mb-0">
            <li class="d-flex flex-column align-items-end ms-2">
                <div class="progress" role="progressbar" aria-valuenow="<?= $diskUsage['percent'] ?>" aria-valuemin="0" aria-valuemax="100" style="height: 2px; width: 100%;">
                    <div class="progress-bar" style="width: <?= $diskUsage['percent'] ?>%"></div>
                </div>
                <?= $diskUsage['free'] ?> free of <?= $diskUsage['total'] ?>
            </li>
            <li class="ms-2"><a class="report-bug" href="https://github.com/Jcarrasco96/tinyexplorer/issues"><i class="bi bi-bug"></i> <?= App::t('Report issue') ?></a></li>
        </ul>
    </footer>
</div>
