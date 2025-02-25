<?php

use TE\core\App;
use TE\helpers\Utils;
use TE\services\FileSystem;

/** @var string $p */
/** @var ?string $f */
/** @var array $arrFolders */
/** @var ?string $type */
/** @var string $file */
/** @var bool $isCopy */

?>

<?php foreach ($arrFolders as $kFolder => $vFolder): ?>
    <div class="col" data-card-id="card-folder-<?= $kFolder ?>">
        <?php if ($isCopy): ?>
            <div class="card card-selection" data-url="<?= Utils::urlTo('site/copy/' . $type . '/' . base64_encode($f) . '?p=' . $vFolder['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 180px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFolder['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFolder['f']) ?></h5></div>
                    <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFolder['modification_date'] ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-selection folder" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/index/' . $vFolder['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 500px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFolder['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFolder['f']) ?></h5></div>
                    <div style="width: 180px;"><?= $vFolder['modification_date'] ?></div>
                    <div style="width: 100px;"><small class="text-body-secondary me-auto"><?= App::t('Folder') ?></small></div>
                    <div class="text-end" style="width: 220px;">
                        <p class="mb-0">
                            <?php if (App::$session->getPermission('cCompress') || App::$session->getPermission('cAdmin')): ?>
                                <button class="btn btn-bd-primary btn-sm" id="btn-compress-zip" title="<?= App::t('Compress to ZIP') ?>" data-url="<?= Utils::urlTo('site/compress/' . base64_encode($vFolder['f'])) ?>"><i class="bi bi-file-earmark-zip" aria-hidden="true"></i></button>
                            <?php endif; ?>

                            <?php if (App::$session->getPermission('cCopy') || App::$session->getPermission('cAdmin')): ?>
                                <button class="btn btn-bd-primary btn-sm" id="btn-copy" title="<?= App::t('Copy') ?>" data-url="<?= Utils::urlTo('site/copy/folder/' . base64_encode($p . DIRECTORY_SEPARATOR . $vFolder['f'])) ?>"><i class="bi bi-copy" aria-hidden="true"></i></button>
                            <?php endif; ?>

                            <?php if (App::$session->getPermission('cRename') || App::$session->getPermission('cAdmin')): ?>
                                <button class="btn btn-bd-primary btn-sm" id="btn-rename" title="<?= App::t('Rename') ?>" data-url="<?= Utils::urlTo('site/rename/' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-input-cursor-text" aria-hidden="true"></i></button>
                            <?php endif; ?>

                            <?php if (App::$session->getPermission('cDelete') || App::$session->getPermission('cAdmin')): ?>
                                <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/delete/' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-trash" aria-hidden="true"></i></button>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
