<?php

use app\core\App;
use app\utils\Utils;

?>

<nav id="w0-navbar" class="navbar navbar-expand-md navbar-dark fixed-top" style="background-color: #712cf9;">
    <div class="container">
        <a class="navbar-brand" href="<?= Utils::urlTo('site/index') ?>">
            <span class="title"><i class="bi bi-folder2-open"></i> Tiny<strong>Explorer</strong></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-1"><a class="nav-link" href="<?= Utils::urlTo('site/change-theme') ?>"><i class="bi <?= App::$system->isLightTheme() ? 'bi-moon' : 'bi-sun' ?>"></i></a></li>

                <li class="nav-item me-1"><a class="nav-link <?= str_contains($_SERVER['PATH_INFO'], 'site/help') ? 'active' : '' ?>" href="<?= Utils::urlTo('site/help') ?>"><i class="bi bi-question-lg"></i> Help</a></li>
                <li class="nav-item me-1"><a class="nav-link <?= str_contains($_SERVER['PATH_INFO'], 'site/settings') ? 'active' : '' ?>" href="<?= Utils::urlTo('site/settings') ?>"><i class="bi bi-gear"></i> Settings</a></li>

                <?php if (App::$session->isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= Utils::urlTo('auth/logout') ?>" data-method="post"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= Utils::urlTo('auth/login') ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>