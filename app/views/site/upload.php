<?php

/** @var string $p */

use app\utils\Utils;

?>

<div class="container">

    <div class="d-flex align-items-center mb-2">
        <a class="btn btn-bd-primary me-2" href="<?= Utils::urlTo('site/index?p=' .  base64_encode($p)) ?>"><i class="bi bi-chevron-left"></i></a>
    </div>

    <div class="my-dropzone dropzone">
        <div class="dz-message text-body-tertiary">Drag your files here or click to upload.</div>
    </div>

</div>

<script>
    const url = "<?= Utils::urlTo('api/upload?p=' . base64_encode($p)) ?>";

    const dropzone = new Dropzone("div.my-dropzone", {
        url: url,
        chunking: true,
        chunkSize: 2000000,
        forceChunking: true,
        retryChunks: true,
        retryChunksLimit: 3,
        parallelUploads: 1,
        parallelChunkUploads: false,
        timeout: 120000,
        maxFilesize: 5000000000,
        acceptedFiles : "",
        autoProcessQueue: true,
        init: function () {
            const dropzoneInstance = this;

            document.body.addEventListener("dragover", function (e) {
                e.preventDefault();
                e.stopPropagation();
            });

            document.body.addEventListener("drop", function (e) {
                e.preventDefault();
                e.stopPropagation();
                const files = e.dataTransfer.files;
                if (files.length) {
                    Array.from(files).forEach(file => dropzoneInstance.addFile(file));
                }
            });

            dropzoneInstance.on("queuecomplete", function () {
                // window.location.reload();
            });

            dropzoneInstance.on("sending", function (file, xhr, formData) {
                formData.set('fullpath', (file.fullPath) ? file.fullPath : file.name);
                xhr.ontimeout = (function() {
                    nerror('Error: Server Timeout');
                });
            });

            dropzoneInstance.on("success", function (res) {
                let _response = JSON.parse(res.xhr.response);

                if(_response.status === "error") {
                    nerror(_response.message);
                } else {
                    nsuccess(_response.message);
                }
            });

            dropzoneInstance.on("error", function(file, response) {
                nerror(response);
            });
        }
    });

</script>
