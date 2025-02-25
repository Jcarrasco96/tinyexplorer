<?php

use TE\core\App;
use TE\helpers\Html;
use TE\helpers\Utils;

/** @var string $p */

?>

<nav id="w0-navbar" class="navbar navbar-expand-md navbar-dark fixed-top" style="background-color: #712cf9;">
    <div class="container px-3">
        <a class="navbar-brand" href="<?= Utils::urlTo('site/index') ?>">
            <img src="<?= Html::img('folder_24.png') ?>" alt="Logo" class="d-inline-block align-text-top"/>
            <span class="title">Tiny<strong>Explorer</strong></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">

            <ul class="navbar-nav ms-auto"></ul>

            <?php if (App::$session->isLoggedIn()): ?>
                <form class="d-flex" role="search" action="<?= Utils::urlTo('site/index/' . App::$session->path()) ?>">
                    <input class="form-control me-2 focus-ring focus-ring-secondary" type="search" placeholder="Search..." aria-label="Search" name="s" value="<?= $_GET['s'] ?? '' ?>">
                </form>
            <?php endif; ?>

            <ul class="navbar-nav">
                <?php if (App::$session->isLoggedIn()): ?>
                    <li class="nav-item me-1"><button id="btn-change-theme" type="button" class="nav-link" data-url="<?= Utils::urlTo('site/change-theme') ?>"><i class="bi <?= App::$system->isLightTheme() ? 'bi-moon' : 'bi-sun' ?>"></i></button></li>

                    <?php if (App::$session->getPermission('cAdmin')): ?>
                        <li class="nav-item me-1"><a class="nav-link <?= str_contains($_SERVER['PATH_INFO'], 'admin/users') ? 'active' : '' ?>" href="<?= Utils::urlTo('admin/users') ?>"><i class="bi bi-people"></i> <?= App::t('Users') ?></a></li>
                        <li class="nav-item me-1"><a class="nav-link <?= str_contains($_SERVER['PATH_INFO'], 'site/settings') ? 'active' : '' ?>" href="<?= Utils::urlTo('site/settings') ?>"><i class="bi bi-gear"></i> <?= App::t('Settings') ?></a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link" href="<?= Utils::urlTo('auth/logout') ?>" data-method="post"><i class="bi bi-box-arrow-right"></i> <?= App::t('Logout') ?></a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= Utils::urlTo('auth/login') ?>"><?= App::t('Login') ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
    $(document).on('click', "#btn-change-theme", function (event) {
        event.preventDefault();

        let button = $(this);

        button.addClass('disabled');

        $.ajax({
            url: $(this).data('url'),
            dataType: 'json',
            success: function() {
                window.location.reload();
            },
            error: function(jqXHR, textStatus) {
                nerror(textStatus);
            },
            complete: function () {
                button.removeClass('disabled');
            }
        });

        return false;
    });
</script>