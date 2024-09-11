jQuery(document).ready(function($) {
    // Mostrar popup por 3 segundos y refrescar la página
    var popup = $('#popup-message');
    if (popup.length) {
        setTimeout(function() {
            popup.fadeOut();
        }, 3000);
    }
});