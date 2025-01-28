<?php

use app\core\App;
use app\helpers\Html;
use app\utils\Utils;

/** @var $data array|null */
/** @var $error string|null */

?>

<style>
    #content {
        background-color: #712cf9;
    }
</style>

<section class="container register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                <div class="card">

                    <div class="card-body">

                        <div class="text-center">
                            <img src="<?= Html::img('folder_64.png') ?>" alt="Logo"/>

                            <h1 class="display-4">Tiny<strong>Explorer</strong></h1>

                            <h5 class="card-title fs-4"><?= App::t('Login to your account') ?></h5>
                            <p class="small"><?= App::t('Enter your username and password to login') ?></p>
                        </div>

                        <form class="row g-3 needs-validation" method="post" novalidate>

                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(App::$session->_csrf()) ?>">

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="email" name="email" value="<?= $data['email'] ?? '' ?>" class="form-control <?= isset($error['email']) ? 'is-invalid' : 'is-valid' ?>" id="floatingInput" placeholder="name@example.com" required>
                                    <label for="floatingInput"><?= App::t('Email address') ?></label>
                                    <div class="invalid-feedback"><?= $error['email'] ?? App::t('Please enter your email.') ?></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating mb-0">
                                    <input type="password" name="password" class="form-control <?= isset($error['password']) ? 'is-invalid' : 'is-valid' ?>" id="floatingInput2" placeholder="" required>
                                    <label for="floatingInput2"><?= App::t('Password') ?></label>
                                    <div class="invalid-feedback"><?= $error['password'] ?? App::t('Please enter your password.') ?></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-bd-primary btn-lg w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> <?= App::t('Login') ?></button>
                            </div>

                            <div class="col-12 d-none">
                                <p class="small mb-0"><?= App::t("Don't have account?") ?> <a href="<?= Utils::urlTo('auth/register') ?>"><?= App::t('Create an account') ?></a></p>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>

    document.querySelectorAll('.needs-validation').forEach(form =>
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false)
    );

    const select = (el, all = false) => {
        return all ? [...document.querySelectorAll(el.trim())] : document.querySelector(el.trim());
    }

    let backToTop = select('.back-to-top');
    if (backToTop) {
        const toggleBackToTop = () => backToTop.classList.toggle('active', window.scrollY > 100);
        window.addEventListener('load', toggleBackToTop);
        document.addEventListener('scroll', toggleBackToTop)
    }

</script>
