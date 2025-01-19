<?php

/** @var string $jwt */

use app\core\App;
use app\helpers\Html;
use app\utils\Utils;

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