<?php

use TE\core\App;
use TE\helpers\Breadcrumb;
use TE\helpers\Utils;
use TE\services\FileSystem;

/** @var mixed $result */
/** @var ?string $parent */
/** @var string $p */

$arrFolders = array_filter($result, function ($f) {
    return !$f['isFile'];
});
$arrFiles = array_filter($result, function ($f) {
    return $f['isFile'];
});

/** @var string $execTime */

$path = App::$system->rootPath;
if ($p != '') {
    $path .= '/' . $p;
}

function buscarArchivosScandir($directorio) {
    $coincidencias = [];
    $archivos = scandir($directorio);

    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') {
            continue;
        }

        $rutaCompleta = $directorio . '/' . $archivo;

        if (is_dir($rutaCompleta)) {
            $coincidencias = array_merge($coincidencias, buscarArchivosScandir($rutaCompleta));
        } elseif (is_file($rutaCompleta)) {
            $coincidencias[] = $rutaCompleta;
        }
    }

    return $coincidencias;
}

function buscarArchivosOpendir($directorio, $filter) {
    $coincidencias = [];
    if ($handle = opendir($directorio)) {
        while (false !== ($archivo = readdir($handle))) {
            if ($archivo === '.' || $archivo === '..') {
                continue;
            }

            $rutaCompleta = $directorio . '/' . $archivo;

            if (is_dir($rutaCompleta)) {
                $coincidencias = array_merge($coincidencias, buscarArchivosOpendir($rutaCompleta, $filter));
            } elseif (is_file($rutaCompleta) && str_contains($archivo, $filter)) {
                $coincidencias[] = $rutaCompleta;
            }
        }
        closedir($handle);
    }
    return $coincidencias;
}

//$inicio = microtime(true);
//$archivos = buscarArchivosScandir('F:\\Programming');
//echo "scandir(): " . number_format(microtime(true) - $inicio, 4) . " segundos\n";

//$inicio = microtime(true);
//$archivos = buscarArchivosOpendir('F:/Music', 'cuando');
//echo "opendir() + readdir(): " . number_format(microtime(true) - $inicio, 4) . " segundos\n";

function buscarArchivos($directorio, $filtro) {
    $coincidencias = [];

    $archivos = glob($directorio . '/*' . $filtro . '*');

    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            $coincidencias[] = $archivo;
        }
    }

    if ($handle = opendir($directorio)) {
        while (false !== ($elemento = readdir($handle))) {
            if ($elemento === '.' || $elemento === '..') {
                continue;
            }

            $rutaCompleta = $directorio . '/' . $elemento;

            if (is_dir($rutaCompleta)) {
                $coincidencias = array_merge($coincidencias, buscarArchivos($rutaCompleta, $filtro));
            }
        }
        closedir($handle);
    }

    return $coincidencias;
}

$inicio = microtime(true);
$archivos = buscarArchivos('Z:/', 'vzdump-lxc-101');
echo "opendir() + readdir() and glob(): " . number_format(microtime(true) - $inicio, 4) . " segundos\n";

echo "<pre>";
print_r($archivos);
echo "</pre>";

?>

<div class="container">

    <h3><?= $execTime ?> ms</h3>

    <div class="d-flex align-items-center justify-content-between mb-2">
        <?php if ($parent !== false): ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index?p=' .  base64_encode($parent)) ?>"><i class="bi bi-chevron-left"></i></a>
            <?= Breadcrumb::run(['path' => $p]) ?>
        <?php else: ?>
            <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index') ?>"><i class="bi bi-house"></i></a>
        <?php endif; ?>

        <p class="ms-2 mb-0">
            <button type="button" class="btn btn-bd-primary" id="btn-new" data-url="<?= Utils::urlTo('site/new-ajax?p=' . base64_encode($p)) ?>" data-type="file"><i class="bi bi-file-plus"></i> <?= App::t('New file') ?></button>
            <button type="button" class="btn btn-bd-primary" id="btn-new" data-url="<?= Utils::urlTo('site/new-ajax?p=' . base64_encode($p)) ?>" data-type="folder"><i class="bi bi-folder-plus"></i> <?= App::t('New folder') ?></button>
            <a class="btn btn-bd-primary" href="<?= Utils::urlTo('site/upload-link?p=' . base64_encode($p)) ?>"><i class="bi bi-upload"></i> <?= App::t('Upload') ?></a>
        </p>
    </div>

    <div class="row row-cols-1 g-1">
        <?php foreach ($arrFolders as $kFolder => $vFolder): ?>
            <div class="card card-selection" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/index?p=' . $vFolder['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 450px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFolder['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFolder['encFile']) ?></h5></div>
                    <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFolder['modification_date'] ?></div>
                    <div style="width: 100px;"><small class="text-body-secondary me-auto"><?= App::t('Folder') ?></small></div>
                    <div class="text-end" style="width: 170px;">
                        <p class="mb-0">
                            <button class="btn btn-bd-primary btn-sm" id="btn-compress-zip" title="<?= App::t('Compress to ZIP') ?>" data-url="<?= Utils::urlTo('site/compress?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>"><i class="bi bi-file-earmark-zip" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-rename" title="<?= App::t('Rename') ?>" data-url="<?= Utils::urlTo('site/rename?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-input-cursor-text" aria-hidden="true"></i></button>
                            <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-card-id="card-folder-<?= $kFolder ?>" data-url="<?= Utils::urlTo('site/delete?p=' . base64_encode($p) . '&f=' . base64_encode($vFolder['f'])) ?>" data-type="folder"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($arrFiles as $kFile => $vFile): ?>
            <div class="card card-selection" data-card-id="card-file-<?= $kFile ?>" data-url="<?= Utils::urlTo('site/view/' . $vFile['link']) ?>">
                <div class="row card-body px-3 py-2 align-items-center">
                    <div class="col-auto" style="width: calc(100% - 450px);"><h5 class="text-nowrap mb-0 py-1 text-truncate"><i class="<?= $vFile['bi_icon'] ?>"></i> <?= FileSystem::convertWin($vFile['encFile']) ?></h5></div>
                    <div class="d-nones d-lg-table-cells" style="width: 180px;"><?= $vFile['modification_date'] ?></div>
                    <div style="width: 100px;"><small class="text-body-secondary me-auto"><?= $vFile['filesize'] ?></small></div>
                    <div class="text-end" style="width: 170px;">
                        <p class="mb-0">
                            <button class="btn btn-bd-primary btn-sm" id="btn-share" title="<?= App::t('Share') ?>" data-url="<?= Utils::urlTo('site/share?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>"><i class="bi bi-share" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-download" title="<?= App::t('Download') ?>" data-url="<?= Utils::urlTo('site/download?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>"><i class="bi bi-download" aria-hidden="true"></i></button>
                            <button class="btn btn-bd-primary btn-sm" id="btn-rename" title="<?= App::t('Rename') ?>" data-url="<?= Utils::urlTo('site/rename?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>" data-type="file"><i class="bi bi-input-cursor-text" aria-hidden="true"></i></button>
                            <button class="btn btn-danger btn-sm" id="btn-delete" title="<?= App::t('Delete') ?>" data-card-id="card-file-<?= $kFile ?>" data-url="<?= Utils::urlTo('site/delete?p=' . base64_encode($p) . '&f=' . base64_encode($vFile['f'])) ?>" data-type="file"><i class="bi bi-trash" aria-hidden="true"></i></button>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($arrFolders) && empty($arrFiles)): ?>
            <div class="col">
                <div class="card card-disabled">
                    <div class="card-body px-3 py-2">
                        <h5 class="mb-0 py-1"><i class="bi bi-folder-x"></i> <em>Folder is empty</em></h5>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
