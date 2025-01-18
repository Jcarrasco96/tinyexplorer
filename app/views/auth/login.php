<?php

use app\core\App;
use app\core\Html;
use app\core\Utils;

/** @var $data array|null */
/** @var $error string|null */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - <?= App::$config['name'] ?></title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <?= Html::icon("img/favicon.png") ?>
    <?= Html::icon("img/apple-touch-icon.png", "apple-touch-icon") ?>

    <?= Html::css("bootstrap.min.css") ?>
    <?= Html::css("bootstrap-icons/bootstrap-icons.min.css") ?>
    <?= Html::css("preloader.css") ?>
    <?= Html::css("style.css") ?>

    <style>
        #content {
            background-color: #712cf9;
        }
    </style>

</head>
<body>

<main id="content" style="display: none;">

    <section class="container register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-center">
                                <img src="<?= Html::img('logo.png') ?>"  alt="Logo"/>
                                <h5 class="card-title fs-4">Login to your account</h5>
                                <p class="small">Enter your username and password to login</p>
                            </div>

                            <form class="row g-3 needs-validation" method="post" novalidate>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" name="email" value="<?= $data['email'] ?? '' ?>" class="form-control <?= isset($error['email']) ? 'is-invalid' : 'is-valid' ?>" id="floatingInput" placeholder="name@example.com" required>
                                        <label for="floatingInput">Email address</label>
                                        <div class="invalid-feedback"><?= $error['email'] ?? 'Please enter your email.' ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-0">
                                        <input type="password" name="password" class="form-control <?= isset($error['password']) ? 'is-invalid' : 'is-valid' ?>" id="floatingInput2" placeholder="" required>
                                        <label for="floatingInput2">Password</label>
                                        <div class="invalid-feedback"><?= $error['password'] ?? 'Please enter your password.' ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-bd-primary btn-lg w-100" type="submit">Login</button>
                                </div>

                                <div class="col-12">
                                    <p class="small mb-0">Don't have account? <a href="<?= Utils::urlTo('auth/register') ?>">Create an account</a></p>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>

</main>

<div class="preloader"></div>
<button class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i> Up</button>

<?= Html::js("jquery-3.7.1.min.js") ?>
<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("index.js") ?>

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

</body>
</html>