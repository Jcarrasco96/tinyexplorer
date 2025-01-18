<?php


?>

<div class="modal fade" id="modal-app" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-app-title">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-app-container"></div>
        </div>
    </div>
</div>

<script>
    $('#modal-app').on('hidden.bs.modal', () => {
        $("#modal-app-container").empty();
        $("#modal-app-title").empty();

    });

    $(document).on("click", "#btn-new", function (event) {
        event.preventDefault();

        let title = $(this).data('type') === 'file' ? 'New file' : 'New folder';
        let url = $(this).data('url') + '&t=' + $(this).data('type');

        $("#modal-app-title").html(title);

        $.ajax({
            type: 'get',
            url: url
        }).done(function (response) {
            $("#modal-app-container").html(response);
            $("#modal-app").modal('show');
        });

        return false;
    });

    $(document).on('submit', "#form-new", function (event) {
        event.preventDefault();

        let buttonSubmit = $(this).find(":submit");
        let content = buttonSubmit.html();

        buttonSubmit.html("<i class='bi-hourglass'></i> Loading...");

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serializeArray(),
            success: function(data) {
                /** @var {number} data.status */
                /** @var {array} data.error */
                /** @var {string} data.message */

                $('#invalid-name').html(data.error.name || '');

                if (data.error.name) {
                    $('#inputName').removeClass('is-valid').addClass('is-invalid');
                } else {
                    $('#inputName').removeClass('is-invalid').addClass('is-valid');
                }

                buttonSubmit.html(content);

                if (data.status === 200) {
                    $("#modal-app").modal('hide');
                    window.location.reload();
                }
            },
            error: function(jqXHR, textStatus) {
                nerror(textStatus);
            }
        });

        return false;
    });
</script>