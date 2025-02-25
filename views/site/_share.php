<?php

use TE\core\App;
use TE\helpers\Html;
use TE\helpers\Utils;

/** @var string $jwt */

$directLink = Utils::urlTo('api/dd?t=' . $jwt);

?>

<p><?= App::t('Copy this link or scan the QR code to download the file directly.') ?></p>

<p class="text-break user-select-all border rounded p-2"><?= $directLink ?></p>

<div id="qr-container">
    <canvas id="qr-code"></canvas>
</div>

<style>
    #qr-container {
        display: flex;
        flex-direction: column;
        align-items: start;
    }
</style>

<?= Html::js("qrious/qrious.js") ?>

<script>
    new QRious({
        element: document.getElementById('qr-code'),
        size: 250,
        value: "<?= $directLink ?>"
    });
</script>