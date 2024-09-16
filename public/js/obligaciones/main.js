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


$(document).ready(function() {
    // Manejador de clic para las tarjetas de evidencia
    $('.derivation-card').on('click', function() {
        var evidenciaId = $(this).data('id');
        var requisitoId = $(this).data('requisito-id');
        
        // Realiza una solicitud AJAX para obtener los detalles de la evidencia
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            
            type: 'POST',
            url: '/ruta-para-obtener-detalles', // Cambia esta URL a la ruta correspondiente en tu controlador
            data: {
                evidencia_id: evidenciaId
            },
            
            success: function(response) {
                // Actualiza el contenedor info-container con la información recibida
                $('#info' + requisitoId).html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener los detalles de la evidencia:', error);
            }
        });
    });
});

