function nsuccess(message, onClosed = () => {}) {
    notify(message, 'success', 'bi-check-lg', onClosed);
}

function nerror(message, onClosed = () => {}) {
    notify(message, 'danger', 'bi-x-lg', onClosed);
}

function ninfo(message, onClosed = () => {}) {
    notify(message, 'info', 'bi-info-circle', onClosed);
}

function nwarning(message, onClosed = () => {}) {
    notify(message, 'warning', 'bi-exclamation-triangle', onClosed);
}

function nprimary(message, onClosed = () => {}) {
    notify(message, 'primary', 'bi-exclamation-triangle', onClosed);
}

function notify(message, type, icon, onClosed = () => {}) {
    $.notify({
        'message': message,
        'icon': icon,
    }, {
        'type': type,
        'delay': 3000,
        'placement': {
            'from': 'bottom',
            'align': 'right'
        },
        'animate': {
            'enter': 'animated fadeInRight', // fadeInUp
            'exit': 'animated fadeOutRight' // fadeOutDown
        },
        'newest_on_top': false,
        'offset': {
            'x': 10,
            'y': 10
        },
        onClosed: onClosed
    });
}
