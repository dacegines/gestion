$(document).ready(function() {
    // Manejar la entrada de texto en el campo de búsqueda
    $('#buscarInput').on('input', function() {
        let searchText = $(this).val().toLowerCase();
        $('#cajaContainer .col-md-2').each(function() {
            let cardText = $(this).text().toLowerCase();
            $(this).toggle(cardText.includes(searchText));
        });
    });

    // Manejar el clic en las tarjetas de opción para abrir el modal
    $('.option-card[data-toggle="modal"]').on('click', function() {
        let targetModal = $(this).data('target');
        $(targetModal).modal('show');
    });


});






document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los elementos con la clase "status-indicator"
    const statusIndicators = document.querySelectorAll('.status-indicator');

    statusIndicators.forEach(function(indicator) {
        // Comprobar si el texto del indicador es "Completo"
        if (indicator.textContent.trim() === 'Completo') {
            // Cambiar el color de fondo a verde
            indicator.style.backgroundColor = 'green';
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los elementos con la clase "status-indicator"
    const statusIndicators = document.querySelectorAll('.status-indicator');

    statusIndicators.forEach(function(indicator) {
        // Comprobar si el texto del indicador es "Completo"
        if (indicator.textContent.trim() === 'Completo') {
            // Cambiar el color de fondo a verde
            indicator.style.backgroundColor = 'green';
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const avances = document.querySelectorAll('.avance-obligacion');

    avances.forEach(function (avance) {
        const valorAvance = parseInt(avance.getAttribute('data-avance'), 10);
        let colorClase = '';

        if (valorAvance >= 0 && valorAvance <= 15) {
            colorClase = 'avance-rojo';
        } else if (valorAvance >= 16 && valorAvance <= 50) {
            colorClase = 'avance-naranja';
        } else if (valorAvance >= 51 && valorAvance <= 99) {
            colorClase = 'avance-amarillo';
        } else if (valorAvance == 100) {
            colorClase = 'avance-verde';
        }

        avance.classList.add(colorClase);
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const avances = document.querySelectorAll('.avance-obligacion');

    avances.forEach(function (avance) {
        const valorAvance = parseInt(avance.getAttribute('data-avance'), 10);
        let colorClase = '';

        if (valorAvance >= 0 && valorAvance <= 15) {
            colorClase = 'avance-rojo';
        } else if (valorAvance >= 16 && valorAvance <= 50) {
            colorClase = 'avance-naranja';
        } else if (valorAvance >= 51 && valorAvance <= 99) {
            colorClase = 'avance-amarillo';
        } else if (valorAvance == 100) {
            colorClase = 'avance-verde';
        }

        avance.classList.add(colorClase);
    });
});


