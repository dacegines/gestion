
$(function() {
    // Inicializar eventos arrastrables
    function ini_events(ele) {
        ele.each(function() {
            var eventObject = {
                title: $.trim($(this).text()) // Título del evento
            };
            $(this).data('eventObject', eventObject);

            $(this).draggable({
                zIndex: 1070,
                revert: true, // El evento vuelve a su lugar si no se suelta en el calendario
                revertDuration: 0 // Duración de la animación al volver
            });
        });
    }

    // Inicializar eventos arrastrables
    ini_events($('#external-events div.external-event'));

    // Inicializar el calendario
    $('#calendar').fullCalendar({
        locale: 'es',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: true,
        droppable: true, // Permitir que los eventos arrastrables se agreguen al calendario
        height: 700,
        events: eventosCalendarioUrl, // URL para cargar eventos dinámicos
        eventClick: function(event) {
            // Muestra los datos en el modal
            $('#eventTitle').text(event.title); // Título del evento
            $('#eventDate').text(event.start.format('YYYY-MM-DD')); // Fecha del evento
            $('#eventDescription').text(event.description ||
            'No hay descripción disponible.'); // Descripción
            $('#eventResponsable').text(event.responsable ||
            'No asignado'); // Responsable del evento

            // Mostrar el estado aprobado
            const eventApproved = $('#eventApproved');
            if (event.approved == 1) {
                eventApproved.text('Esta evidencia ha sido marcada como revisada.');
                eventApproved.removeClass('bg-danger').addClass(
                'bg-success text-white'); // Verde
            } else {
                eventApproved.text(
                    'Esta evidencia no ha sido revisada o volvió a su estatus inicial.');
                eventApproved.removeClass('bg-success').addClass(
                'bg-danger text-white'); // Rojo
            }

            // Mostrar el modal
            $('#eventModal').modal('show');
        },
        eventRender: function(event, element) {
            // Tooltip con el título del evento
            element.attr('title', event.title);

            // Cambiar el color según condiciones
            if (event.approved == 1) {
                element.css('background-color', '#28a745'); // Verde
                element.css('border-color', '#28a745');
            } else {
                element.css('background-color', '#dc3545'); // Rojo
                element.css('border-color', '#dc3545');
            }
        }
    });

    // Configurar selector de colores para eventos
    $('#color-chooser > li > a').click(function(e) {
        e.preventDefault();
        var currColor = $(this).css('color');
        $('#add-new-event').css({
            'background-color': currColor,
            'border-color': currColor
        });
    });

    // Agregar nuevo evento
    $('#add-new-event').click(function(e) {
        e.preventDefault();
        var val = $('#new-event').val();
        if (!val.length) return;

        var event = $('<div />');
        event.css({
                'background-color': '#3c8dbc',
                'border-color': '#3c8dbc',
                'color': '#fff'
            })
            .addClass('external-event')
            .text(val);
        $('#external-events').prepend(event);
        ini_events(event);
        $('#new-event').val('');
    });
});
