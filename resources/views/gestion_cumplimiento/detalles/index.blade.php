@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
<hr>
<div class="card">
    <div class="card-header-title card-header bg-success text-white text-center">
        <h4 class="card-title-description">Detalles</h4>
    </div>

    <div class="card-body">
        <div class="row justify-content-center">
            <form action="{{ route('filtrar-detalle') }}" method="POST" class="form-inline d-flex align-items-center">
                @csrf
                <div class="form-group mr-2">
                    <label for="year-select" class="mr-2">Año:</label>
                    <select id="year-select" name="year" class="form-control form-control-sm">
                        @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                            <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endfor
                    </select>
                </div>
        
                <button type="submit" class="btn btn-success btn-sm">Ver</button>
            </form>
        </div>

        <div class="divider"></div>

        <div class="table-responsive">
            <table id="detallesTable" class="table table-striped table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>#Obligación</th>
                        <th>Cláusula</th>
                        <th>Obligación</th>
                        <th>Periodicidad</th>
                        <th>Adjuntos</th>
                        <th>Fecha límite</th>
                        <th>Responsable</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requisitos as $requisito)
                    <tr>
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->numero_evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                        <td style="text-align: justify; font-size: 12px;" >{{ htmlspecialchars($requisito->clausula, ENT_QUOTES, 'UTF-8') }}</td>
                        
                        {{-- Utiliza el alias `requisito_evidencia` para la evidencia de `requisitos` --}}
                        <td style="text-align: justify; font-size: 12px;">{{ htmlspecialchars($requisito->requisito_evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                        
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->periodicidad, ENT_QUOTES, 'UTF-8') }}</td>
                
                        {{-- Columna de Adjuntos, mostrando los archivos si están disponibles --}}
                        <td  class="adjuntos-link" data-fecha="{{ $requisito->fecha_limite_cumplimiento }}">
                            @if($requisito->cantidad_archivos > 0)
                                <span style="cursor: pointer; color: blue; text-decoration: underline;">
                                    {{ $requisito->cantidad_archivos }} archivo{{ $requisito->cantidad_archivos > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span style="font-size: 12px;">No hay adjuntos</span>
                            @endif
                        </td>
                                             

                        <td style="white-space: nowrap; font-size: 12px;">
                            {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
                        </td>
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->responsable, ENT_QUOTES, 'UTF-8') }}</td>
                        <td >
                            <span class="badge badge-{{ $requisito->estatus === 'Cumplido' ? 'success' : ($requisito->estatus === 'Vencido' ? 'danger' : ($requisito->estatus === 'Próximo a Vencer' ? 'warning' : 'info')) }}">
                                {{ $requisito->estatus }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<!-- Modal para mostrar los archivos -->
<div class="modal fade" id="modalArchivos" tabindex="-1" role="dialog" aria-labelledby="modalArchivosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalArchivosLabel">Archivos Adjuntos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="lista-archivos"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
$(document).ready(function() {
    // Detectar clic en el texto de adjuntos para abrir el modal
    $('.adjuntos-link').on('click', function() {
        const fechaLimite = $(this).data('fecha');

        // Hacer una solicitud GET a la nueva ruta usando la fecha límite
        axios.get(`{{ url('/obtener-archivos') }}/${fechaLimite}`)
            .then(response => {
                const archivos = response.data;
                $('#lista-archivos').empty();

                if (archivos.length > 0) {
                    archivos.forEach((archivo, index) => {
                        // Extraer la parte del nombre del archivo desde el primer guion bajo
                        const nombreVisible = archivo.nombre_archivo.substring(archivo.nombre_archivo.indexOf('_') + 1);

                        $('#lista-archivos').append(
                            `<li><a href="{{ asset('storage') }}/${archivo.ruta_archivo}" target="_blank">Doc ${index + 1}: ${nombreVisible}</a></li>`
                        );
                    });
                }else {
                    $('#lista-archivos').append('<li>No hay archivos adjuntos</li>');
                }

                $('#modalArchivos').modal('show');
            })
            .catch(error => {
                console.error('Error al obtener archivos:', error);
            });
    });
});

    </script>
    
    

    <script src="{{ asset('js/gestion_obligaciones/detalles/detalles.js') }}"></script>
@stop
