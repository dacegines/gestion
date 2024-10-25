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
                        @if (Auth::user()->hasRole('invitado'))
                            <!-- Solo mostrar 2024 si el usuario es 'invitado' -->
                            @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                                <option value="{{ $yearOption }}" {{ (isset($year) && $year == $yearOption) ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endfor
                        @else
                            <!-- Mostrar todos los años si no es 'invitado' -->
                            @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                                <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                    {{ $yearOption }}
                                </option>
                            @endfor
                        @endif
                    </select>
                </div>
        
                <!-- Botón Ver, deshabilitado si el usuario es invitado -->
                <button type="submit" class="btn btn-success btn-sm"
                        @if (Auth::user()->hasRole('invitado')) disabled @endif>Ver</button>
            </form>
        </div>

        <div class="container-fluit text-center">
            <!-- Mostrar el mensaje si el usuario es 'invitado' y centrar el texto -->
            @if (Auth::user()->hasRole('invitado'))
                <p class="text-danger mt-2"><b>Actualmente eres un usuario invitado y solo tienes acceso a esta información.</b></p>
            @endif
        </div>        

        <div class="divider"></div>

        <div class="table-responsive">
            <table id="detallesTable" class="table table-striped table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th style="font-size: 12px;">#Obligación</th>
                        <th style="font-size: 12px;">Cláusula</th>
                        <th style="font-size: 12px;">Obligación</th>
                        <th style="font-size: 12px;">Periodicidad</th>
                        <th style="font-size: 12px;">Adjuntos</th> <!-- Cambiado de "Avance" a "Adjuntos" -->
                        <th style="font-size: 12px;">Fecha límite</th>
                        <th style="font-size: 12px;">Responsable</th>
                        <th style="font-size: 12px;">Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(Auth::user()->hasRole('invitado') ? $requisitos->take(2) : $requisitos as $requisito)
                    <tr>
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->numero_evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                        
                        <!-- Mostrar mensaje "Sin acceso a esta información" si el usuario es invitado -->
                        <td style="text-align: justify; font-size: 12px;">
                            @if (Auth::user()->hasRole('invitado'))
                                <span class="text-danger">Sin acceso a esta información.</span>
                            @else
                                {{ htmlspecialchars($requisito->clausula_condicionante_articulo, ENT_QUOTES, 'UTF-8') }}
                            @endif
                        </td>
                        
                        <td style="text-align: justify; font-size: 12px;">{{ htmlspecialchars($requisito->evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->periodicidad, ENT_QUOTES, 'UTF-8') }}</td>
                
                        <!-- Nueva columna para mostrar los adjuntos -->
                        <td style="font-size: 12px;">
                            @if (Auth::user()->hasRole('invitado'))
                                <span class="text-danger">Sin acceso a esta información</span>
                            @else
                                @if($requisito->archivos->isNotEmpty()) <!-- Verificar si hay archivos -->
                                    @foreach($requisito->archivos as $index => $archivo) <!-- Iterar sobre los archivos y numerarlos -->
                                        <?php $extension = pathinfo($archivo->nombre_archivo, PATHINFO_EXTENSION); ?> <!-- Obtener la extensión del archivo -->
                                        <a href="{{ asset('storage/' . $archivo->ruta_archivo) }}" target="_blank">
                                            Doc {{ $index + 1 }}.{{ $extension }} <!-- Mostrar como Adjunto 1.pdf, Adjunto 2.doc, etc. -->
                                        </a><br> <!-- Añadir un salto de línea entre archivos -->
                                    @endforeach
                                @else
                                    <span>No hay adjuntos</span> <!-- Mostrar un mensaje si no hay archivos -->
                                @endif
                            @endif
                        </td>
                
                        <td style="white-space: nowrap; font-size: 12px;">
                            {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
                        </td>
                        <td style="font-size: 12px;">{{ htmlspecialchars($requisito->responsable, ENT_QUOTES, 'UTF-8') }}</td>
                        <td>
                            <span class="badge badge-{{ $requisito->porcentaje === 100 ? 'success' : (\Carbon\Carbon::now()->gt($requisito->fecha_limite_cumplimiento) ? 'danger' : (\Carbon\Carbon::now()->diffInDays($requisito->fecha_limite_cumplimiento, false) <= 30 ? 'warning' : 'info')) }}">
                                {{ $requisito->porcentaje === 100 ? 'Cumplido' : (\Carbon\Carbon::now()->gt($requisito->fecha_limite_cumplimiento) ? 'Vencido' : (\Carbon\Carbon::now()->diffInDays($requisito->fecha_limite_cumplimiento, false) <= 30 ? 'Próximo a Vencer' : 'Activo')) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                
                
                </tbody>
            </table>
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

    <!-- Enlace al archivo JavaScript movido -->
    <script src="{{ asset('js/gestion_obligaciones/detalles/detalles.js') }}"></script>

@stop
