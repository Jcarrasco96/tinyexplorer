function fixContainer() {
    let divHeight = 0;

    const navbar = $('#w0-navbar');

    if (navbar.height()) {
        divHeight = navbar.height();
    } else if (navbar.css('pixelHeight')) {
        divHeight = navbar.css('pixelHeight');
    }

    const objContainer = $('#content');
    objContainer.css('paddingTop', "0px");
    objContainer.css('paddingTop', (divHeight - 40) + "px");
}


$(document).ready(function() {
    fixContainer();
});

$(window).on('resize', function() {
    fixContainer();
});

