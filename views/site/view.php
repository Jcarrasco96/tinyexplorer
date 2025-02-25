<?php

use TE\helpers\Breadcrumb;
use TE\helpers\Crypto;
use TE\helpers\Utils;
use TE\services\FileSystem;

/** @var ?string $p */
/** @var string $file */
/** @var string $type */
/** @var string $url */
/** @var array $arrFiles */
/** @var array $filenames */

?>

<div class="container">

    <div class="d-flex align-items-center mb-2">
        <?php if ($p !== false): ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index/' .  base64_encode($p)) ?>"><i class="bi bi-chevron-left"></i></a>

            <?= Breadcrumb::run(['path' => $p . '/' . $file]) ?>
        <?php else: ?>
            <nav aria-label="breadcrumb" class="me-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a></li>
                </ol>
            </nav>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <?php if ($type == 'video'): ?>
                <div class="preview-video">
                    <video controls src="<?= $url ?>" preload="metadata" playsinline class="border rounded">
                        <source src="<?= $url ?>" type="video/mp4">
                    </video>
                </div>
                <h4 class="display-6"><?= $file ?></h4>
            <?php elseif ($type == 'image'): ?>
                <div class="preview-video">
                    <img class="img-thumbnail" src="<?= $url ?>" alt="" />
                </div>
            <?php elseif ($type == 'audio'): ?>
                <audio controls src="<?= $url ?>" preload="metadata" class="w-100"></audio>
            <?php elseif ($type == 'application'): ?>

                <script>
                    $(document).ready(function() {

                        let link = document.createElement('a');
                        link.target = '_blank';
                        link.href = '<?= $url ?>';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        return false;
                    });
                </script>

<!--                <object style="width: 100%; height: 80vh;" data="--><?php //= $url ?><!--"></object>-->

                <?php if ($filenames): ?>
                    <?php foreach ($filenames as $filename): ?>
                        <code><?= $filename['name'] ?> (<?= FileSystem::filesize($filename['compressed_size']) ?>)</code><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>

                <iframe src="<?= Utils::urlTo('site/share/' . base64_encode($file)) ?>" width="100%" height="370"></iframe>

                <?php
                Crypto::init('294443b5bb7221b16a4c8cf47df19af0');

                $msgEnc = Crypto::encrypt(base64_encode($file));
                $msgDec = Crypto::decrypt($msgEnc['data'], $msgEnc['iv']);
                ?>

                <p>Download file: <?= Utils::urlTo('api/raw?f=' . $msgEnc['data'] . '&i=' . $msgEnc['iv']) ?></p>

            <?php endif; ?>
        </div>
        <div class="col-lg-3">
            <div class="row row-cols-1 g-1">
                <?php foreach ($arrFiles as $kFile => $vFile): ?>
                    <div class="col">
                        <div class="card <?= $file == $vFile['f'] ? 'card-disabled' : 'card-selection' ?>" data-url="<?= Utils::urlTo('site/view/' . base64_encode($vFile['f'])) ?>">
                            <div class="card-body p-2">
                                <h5 class="mb-0"><i class="<?= $vFile['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFile['f']) ?></h5>
                                <small class="text-body-secondary me-auto"><?= $vFile['filesize'] ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<style>
    .preview-video {
        overflow: hidden;
        text-align: center;
    }
    .preview-video video {
        max-width: 100%;
        max-height: 75vh;
        width: auto;
        height: auto;
        object-fit: cover;
    }
    .preview-video img {
        width: 100%;
        height: auto;
        object-fit: cover;
        display: block;
    }
</style>

<script>
    $(document).on("click", ".card-selection", function (event) {
        event.preventDefault();
        window.location.assign($(this).data('url'));
        return false;
    });
</script>
