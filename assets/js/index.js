$(document).ready(function () {

    const btnToTop = $('.btn-to-top');
    let preloader = $('.preloader');

    if (btnToTop) {
        btnToTop.click(function () {
            $('body, html').animate({scrollTop: '0px'}, 500);
        });

        toggleToTop();

        $(window).scroll(function () {
            toggleToTop();
        });

        function toggleToTop() {
            if ($(this).scrollTop() > 100) {
                btnToTop.fadeIn(300);
            } else {
                btnToTop.fadeOut(300);
                btnToTop.blur();
            }
        }
    }

    if (preloader) {
        preloader.remove();
        document.querySelector('#content').removeAttribute('style');
    }

});

$('a[data-method="post"]').on('click', function (e) {
    e.preventDefault(); // Prevent the default link behavior

    const url = $(this).attr('href'); // Get the URL from the link
    const confirmMessage = $(this).data('confirm'); // Optional confirm message

    // If there's a confirmation, show it to the user
    if (confirmMessage && !confirm(confirmMessage)) {
        return; // Exit if user clicks "Cancel"
    }

    // Create a hidden form to send the POST request
    const form = $('<form>', {
        method: 'POST',
        action: url
    });

    // Optional: Add CSRF token input if needed
    /*
    form.append($('<input>', {
        type: 'hidden',
        name: '_csrf',
        value: 'your-csrf-token-here'
    }));
    */

    // Append the form to the body and submit it
    $('body').append(form);
    form.submit();
});
