@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
    <hr>
    <div class="card">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Calendario de Obligaciones de TDC</h4>
        </div>

        <div class="card-body">
            <hr>
            <!-- Contenido del Calendario -->
            <div class="row">
                <!-- Panel de eventos -->
                <!-- <div class="col-md-3">
                        <div class="sticky-top mb-3">

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Eventos arrastrables</h4>
                                </div>
                                <div class="card-body">
                                    <div id="external-events">
                                        <div class="external-event bg-success">Obligacion 1</div>
                                        <div class="external-event bg-warning">Obligacion 2</div>
                                        <div class="external-event bg-info">Obligacion 3</div>
                                        <div class="external-event bg-primary">Obligacion 4</div>
                                        <div class="external-event bg-danger">Obligacion 5</div>
                                        <div>
                                            <label for="drop-remove">
                                                <input type="checkbox" id="drop-remove">
                                                Quitar después de soltar
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Crear evento</h3>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                                        <ul class="fc-color-picker" id="color-chooser">
                                            <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                                            <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                                            <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                                            <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                                            <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                                        </ul>
                                    </div>
                                    <div class="input-group">
                                        <input id="new-event" type="text" class="form-control"
                                            placeholder="Título del evento">
                                        <div class="input-group-append">
                                            <button id="add-new-event" type="button" class="btn btn-primary">Agregar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>-->

                <!-- Nueva sección con información del módulo -->
                <div class="col-md-3">
                    <div class="sticky-top mb-3">
                        <!-- Panel de información -->
                        <div class="card">
                            <div class="card-header bg-success text-white text-center">
                                <h4 class="card-title container">Información del Calendario</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Calendario de Obligaciones</strong>, es una herramienta diseñada para facilitar
                                    la gestión de las obligaciones de TDC.
                                    <br>A continuación, se muestra cómo utilizarlo:
                                </p>
                                <ul>
                                    <li>
                                        <strong>Visualización:</strong> En este calendario se representan las obligaciones
                                        con sus respectivas <strong>fechas límite</strong>. Cada obligación se identifica
                                        por su título, y su color refleja su estado actual.
                                    </li>
                                    <li>
                                        <strong>Interacción:</strong> Al hacer clic en una obligación, puedes acceder a un
                                        <strong>detalle completo</strong>, que incluye su descripción, el responsable
                                        asignado y su estado de aprobación.
                                    </li>
                                    <li>
                                        <strong>Estados:</strong>
                                        <ul>
                                            <li>Los eventos <strong>aprobados</strong> se muestran en <span
                                                    style="color: green; font-weight: bold;">verde</span>.</li>
                                            <li>Los eventos <strong>pendientes</strong> o no aprobados aparecen en <span
                                                    style="color: red; font-weight: bold;">rojo</span>.</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Planificación:</strong> Este calendario te permite anticiparte a tus
                                        obligaciones y planificar tus actividades de manera eficiente, minimizando riesgos
                                        por incumplimientos.
                                    </li>
                                </ul>
                                <p>Consulta regularmente este calendario para estar al día con tus obligaciones de TDC. Esta
                                    es una herramienta para garantizar que todas las obligaciones sean atendidas de forma
                                    adecuada y oportuna.</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Calendario -->
                <div class="col-md-9">
                    <div class="card card-primary">
                        <div class="calendar-container card-body p-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->

    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Detalle del Obligación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Obligación:</strong> <span id="eventTitle"></span></p>
                    <hr>
                    <p><strong>Fecha limite de cumplimiento:</strong> <span id="eventDate"></span></p>
                    <hr>
                    <p><strong>Descripción:</strong> <span id="eventDescription"></span></p>
                    <hr>
                    <p><strong>Responsable:</strong> <span id="eventResponsable"></span></p>
                    <hr>
                    <p>
                        <span id="eventApproved" class="badge w-100"></span>
                        <!-- Agregamos clase badge para estilos dinámicos -->
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <!-- Estilos de FullCalendar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    

    <style>
        /* Aplica estilos solo al calendario dentro del contenedor .calendar-container */
        .calendar-container .fc-widget-header {
            background-color: #343a40;
            /* Fondo oscuro */
            color: #ffffff;
            /* Texto blanco */
            font-weight: bold;
            /* Texto en negrita */
            border: 1px solid #495057;
            /* Borde oscuro */
        }
    
        /* Estilos de los eventos en el calendario */
        .calendar-container .fc-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    
        .calendar-container .fc-event:hover {
            white-space: normal;
            word-wrap: break-word;
            height: auto !important;
            z-index: 1000;
            overflow: visible;
        }
    
        /* Estilos para aprobados */
        .calendar-container .bg-success {
            background-color: #28a745 !important;
            color: #fff !important;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 15px;
        }
    
        /* Estilos para no aprobados */
        .calendar-container .bg-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 15px;
        }
    
        /* Fondo oscuro para el encabezado del calendario */
        .calendar-container .fc-toolbar {
            background-color: #343a40;
            /* Fondo oscuro */
            color: #ffffff;
            /* Texto blanco */
            padding: 10px;
            border-radius: 5px;
        }
    
        /* Estilo para los botones del encabezado del calendario */
        .calendar-container .fc-toolbar button .fc-corner-left{
            background-color: #495057;
            /* Fondo oscuro del botón */
            color: #ffffff;
            /* Texto blanco */
            border: none;
            border-radius: 5px;
            /* Bordes redondeados */
            padding: 5px 10px;
            margin: 2px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* Sombra */
            font-size: 14px;
            /* Tamaño de texto */
        }
    
        /* Botón activo del encabezado */
        .calendar-container .fc-toolbar .fc-button-active {
            background-color: #28a745 !important;
            /* Fondo verde para el botón activo */
            border-color: #28a745 !important;
            color: #ffffff !important;
        }
    
        /* Estilo del texto del título del calendario */
        .calendar-container .fc-toolbar h2 {
            font-size: 1.25rem;
            /* Tamaño del texto */
            color: #ffffff;
            /* Color del texto */
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css') }}">
@stop


@section('js')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>

    <script>
        $(function() {
            function ini_events(ele) {
                ele.each(function() {
                    var eventObject = {
                        title: $.trim($(this).text())
                    };
                    $(this).data('eventObject', eventObject);

                    $(this).draggable({
                        zIndex: 1070,
                        revert: true,
                        revertDuration: 0
                    });
                });
            }

            ini_events($('#external-events div.external-event'));

            $('#calendar').fullCalendar({
                locale: 'es',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                editable: true,
                droppable: true,
                height: 700,
                events: "{{ url('/api/requisitos') }}", // URL para cargar eventos dinámicos
                eventClick: function(event) {
                    // Muestra los datos en el modal
                    $('#eventTitle').text(event.title); // Título del evento
                    $('#eventDate').text(event.start.format('YYYY-MM-DD')); // Fecha del evento
                    $('#eventDescription').text(event.description ||
                        'No hay descripción disponible.'); // Descripción
                    $('#eventResponsable').text(event.responsable ||
                        'No asignado'); // Responsable del evento

                    // Lógica para mostrar el estado aprobado en verde/rojo
                    const eventApproved = $('#eventApproved');
                    if (event.approved == 1) {
                        eventApproved.text(
                            'Esta evidencia ha sido marcada como revisada.'); // Texto en verde
                        eventApproved.removeClass('bg-danger').addClass(
                            'bg-success text-white'); // Verde
                    } else {
                        eventApproved.text(
                            'Esta evidencia no ha sido revisada o volvió a su estatus inicial.'
                        ); // Texto en rojo
                        eventApproved.removeClass('bg-success').addClass(
                            'bg-danger text-white'); // Rojo
                    }

                    // Muestra el modal
                    $('#eventModal').modal('show');
                },
                eventRender: function(event, element) {
                    element.attr('title', event.title); // Tooltip con el título del evento
                },
                eventRender: function(event, element) {
                    // Lógica para cambiar el color según condiciones
                    if (event.approved == 1) {
                        element.css('background-color', '#28a745'); // Verde
                        element.css('border-color', '#28a745');
                    } else {
                        element.css('background-color', '#dc3545'); // Rojo
                        element.css('border-color', '#dc3545');
                    }
                    element.attr('title', event.title); // Tooltip con el título
                }
            });

            $('#color-chooser > li > a').click(function(e) {
                e.preventDefault();
                var currColor = $(this).css('color');
                $('#add-new-event').css({
                    'background-color': currColor,
                    'border-color': currColor
                });
            });

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
    </script>
@stop
