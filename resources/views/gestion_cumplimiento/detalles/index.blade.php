@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
<br>
<div class="card">
    <div class="card-header bg-success text-white text-center">
        <h3 class="">Detalles</h3>
    </div>

    <div class="card-body">
        <div class="divider"></div>
        <div class="table-responsive">
            <table id="detallesTable" class="table table-striped table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th># Registros</th>
                        <th>Número de requisito</th>
                        <th>Obligación</th>
                        <th>Periodicidad</th>
                        <th>Número de evidencia</th>
                        <th>Avance</th>
                        <th>Fecha límite de cumplimiento</th>
                        <th>Responsable</th>
                        <th>Estatus</th>
                        
                    </tr>
                </thead>
                <tbody>
                     @foreach($requisitos as $requisito)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $requisito->numero_requisito }}</td>
                            <td>{{ $requisito->evidencia }}</td>
                            <td>{{ $requisito->periodicidad }}</td>
                            <td>{{ $requisito->numero_evidencia }}</td>
                            <td>
                                @php
                                    $avance = isset($requisito->porcentaje) && is_numeric($requisito->porcentaje) ? $requisito->porcentaje : 0;
                                @endphp

                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                        style="width: {{ $avance }}%;" 
                                        aria-valuenow="{{ $avance }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        {{ $avance }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}</td>
                            <td>{{ $requisito->responsable }}</td>
                            <td>
                                @php
                                    $hoy = \Carbon\Carbon::now();
                                    $fechaLimite = \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento);
                                    if ($requisito->porcentaje == 100) {
                                        $estatus = 'Cumplido';
                                        $badgeClass = 'badge-success';
                                    } elseif ($fechaLimite->isPast()) {
                                        $estatus = 'Vencido';
                                        $badgeClass = 'badge-danger';
                                    } elseif ($fechaLimite->diffInDays($hoy) <= 30) {
                                        $estatus = 'Próximo a vencer';
                                        $badgeClass = 'badge-warning';
                                    } else {
                                        $estatus = 'Activo';
                                        $badgeClass = 'badge-info';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $estatus }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">Detalles de Requisito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="details-card">
                    <div class="info-section">
                        <div class="logo-container text-right">
                            <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo">
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-calendar"></i>
                            <span>Periodicidad:</span>
                            <p id="modalPeriodicidad"></p>
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-user"></i>
                            <span>Responsable:</span>
                            <p id="modalResponsable"></p>
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Fecha límite de cumplimiento:</span>
                            <p id="modalFechaLimite"></p>
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-file-alt"></i>
                            <span>Origen de la obligación:</span>
                            <p id="modalOrigen"></p>
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-book"></i>
                            <span>Cláusula, condicionante, o artículo:</span>
                            <p id="modalClausula"></p>
                        </div>
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones:</span>
                            <ul id="modalNotificaciones"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css')}}">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>   
    
    <script src="{{ asset('js/js/detalles/main.js')}}"></script>   

    <script>
$(document).ready(function() {
    $('#detallesTable').DataTable({
        "language": {
            "lengthMenu": "Mostrar " +
                `<select class="custom-select custom-select-sm form-control form-control-sm" style="font-size: 15px;">
                    <option value='10'>10</option>
                    <option value='25'>25</option>
                    <option value='50'>50</option>
                    <option value='100'>100</option>
                    <option value='-1'>Todo</option>
                </select>` +
                " registros por página",
            "zeroRecords": "No se encontró ningún registro",
            "info": "Mostrando la página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            'search': 'Buscar:',
            'paginate': {
                'next': 'Siguiente',
                'previous': 'Anterior'
            }
        },
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": '<"top"Bfl>rt<"bottom"ip><"clear">', // Cambiar la disposición para alinear en una línea
        "buttons": [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success'
            }
        ]
    });
});



    </script>
@stop
