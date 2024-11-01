
@extends('adminlte::page')

@section('title', 'Obligaciones')

@section('content')
<hr>
<div class="card">
@php
    // Definir los puestos que no deben mostrar el puesto del usuario
    $puestosExcluidos = [
        'Director Jurídico',
        'Directora General',
        'Jefa de Cumplimiento',
        'Director de Finanzas',
        'Director de Operación'
    ];
@endphp

<div class="card-header-title card-header bg-success text-white text-center">
    <h4 class="card-title-description">
        @if (Auth::user()->hasRole('invitado'))
            Obligaciones - Acceso Limitado
        @elseif(in_array($user->puesto, $puestosExcluidos))
            Obligaciones
        @else
            Obligaciones - {{ $user->puesto }}
        @endif
    </h4>
</div>

    <div class="card-body">
         {{-- Filtros de Fecha --}}
         <div class="row justify-content-center">
            <form id="filter-form" method="POST" action="{{ route('filtrar.obligaciones') }}" class="form-inline d-flex align-items-center mt-1">
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
                                <option value="{{ $yearOption }}" {{ (isset($year) && $year == $yearOption) ? 'selected' : '' }}>
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
        

                
        <div class="divider"></div>
        <button class="btn btn-success" onclick="location.reload();">Actualizar</button>
        <div class="row text-center justify-content-center" id="cajaContainer">
            @if (Auth::user()->hasRole('invitado'))
                <!-- Mostrar solo 3 registros si el usuario es 'invitado' -->
                @foreach($requisitos->unique('nombre')->take(2) as $requisito)
                    <div class="col-md-3">
                        <div class="card obligation-card" data-toggle="modal" data-target="#modal{{ $requisito->id }}">
                            <div class="obligation-image">
                                <span class="avance-obligacion" data-avance="{{ $requisito->total_avance }}">{{ $requisito->total_avance }}%</span>
                                <div class="status-indicator">
                                    {{ $requisito->total_avance == 100 ? 'Completo' : 'Avance: En Progreso' }}
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title container">{{ $requisito->nombre }}</h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Mostrar todos los registros para otros roles -->
                @foreach($requisitos->unique('nombre') as $requisito)
                    <div class="col-md-3">
                        <div class="card obligation-card" data-toggle="modal" data-target="#modal{{ $requisito->id }}">
                            <div class="obligation-image">
                                <span class="avance-obligacion" data-avance="{{ $requisito->total_avance }}">{{ $requisito->total_avance }}%</span>
                                <div class="status-indicator">
                                    {{ $requisito->total_avance == 100 ? 'Completo' : 'Avance: En Progreso' }}
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title container">{{ $requisito->nombre }}</h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
    </div>
</div>


<!-- Modales dinámicos para cada requisito -->
<!-- Iterar sobre los requisitos -->
@foreach($requisitos->unique('nombre') as $requisito)
    <div class="modal fade" id="modal{{ $requisito->id }}" tabindex="-1" aria-labelledby="modal{{ $requisito->id }}Label" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal{{ $requisito->id }}Label">{{ $requisito->nombre }} - Obligaciones</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna de Obligaciones -->
                        <div class="col-md-6">
    <h5><b>Obligaciones</b></h5>
    
    <div id="obligacionesContainer{{ $requisito->id }}">
    @foreach($requisitos->where('nombre', $requisito->nombre)->unique('numero_evidencia') as $evidencia)
        <div class="card derivation-card" data-id="{{ $evidencia->id }}" data-requisito-id="{{ $requisito->id }}">
            <div class="card-body n_evidencia" 
                 data-toggle="collapse" 
                 data-target="#collapseEvidencia{{ $evidencia->id }}" 
                 data-evidencia-id="{{ $evidencia->numero_evidencia }}" 
                 data-id-notificaciones="{{ $evidencia->id_notificaciones }}" 
                 data-requisito-id="{{ $requisito->id }}">
                <strong>{{ $evidencia->numero_evidencia }}</strong> {{ $evidencia->evidencia }}
            </div>
        </div>

        <!-- Collapse para mostrar más detalles de la evidencia dentro del modal correspondiente -->
        <div id="collapseEvidencia{{ $evidencia->id }}" class="collapse" aria-labelledby="heading{{ $evidencia->id }}" data-parent="#obligacionesContainer{{ $requisito->id }}">
            @foreach($requisitos->where('nombre', $requisito->nombre)->where('numero_evidencia', $evidencia->numero_evidencia) as $detalle)
                @php
                    // Verifica si el requisito tiene la columna approved en 1
                    $isApproved = $detalle->approved == 1;
                @endphp
                
                <!-- Botón para abrir el modal -->
                <div class="custom-card text-center mb-3 {{ $isApproved ? 'bg-success text-white' : '' }}" 
                data-toggle="modal" 
                data-target="#modalDetalleContent" 
                data-detalle-id="{{ $detalle->id }}" 
                data-evidencia-id="{{ $evidencia->numero_evidencia }}" 
                data-requisito-id="{{ $requisito->id }}" 
                data-fecha-limite-cumplimiento="{{ $detalle->fecha_limite_cumplimiento }}"
                data-numero-requisito="{{ $requisito->numero_requisito }}" onclick="abrirModalDetalle('{{ $detalle->id }}', '{{ $requisito->id }}')">


                
                <span class="load-archivos" 
                   data-requisito-id="{{ $requisito->id }}" 
                   data-evidencia-id="{{ $evidencia->numero_evidencia }}" >
                   {{ \Carbon\Carbon::parse($detalle->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
               </span>
               @if($isApproved)
               <span class="icon-check top-0 start-0 p-2">
                   <i class="fas fa-check"></i>
               </span>
               @endif
           </div>
           
           
                
            @endforeach
        </div>
        
    @endforeach
</div>
</div>

<!-- Columna de Detalles de Evidencia -->
<div class="col-md-6">
    <h5><b>Detalles de Obligación</b></h5>
    <div id="detail-info-{{ $requisito->id }}" class="info-container">
        <!-- Aquí se cargarán los detalles de la evidencia -->
    </div>

    <!-- Nuevo contenedor para las notificaciones -->
    <div id="notificaciones-info-{{ $requisito->id }}" class="info-container">
        <!-- Aquí se cargarán las notificaciones -->
    </div>

    <!-- Nuevo contenedor para la tabla de notificaciones -->
    <div id="tabla-notificaciones-info-{{ $requisito->id }}" class="info-container">
        <!-- Aquí se cargará la tabla de notificaciones -->
    </div>
</div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach


<!-- Modales para cada detalle -->

<!-- Verificar si $detalle existe -->
@if(isset($detalle))
    <!-- Modales para cada detalle -->
    <div class="modal fade" id="modalDetalleContent" tabindex="-1" aria-labelledby="modalDetalleLabel{{ $detalle->id }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable"> <!-- Añadir modal-dialog-scrollable solo aquí -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel{{ $detalle->id }}">Obligación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Sección 1: Detalles de Evidencia -->
                        <div class="col-md-6">
                            <h5><b>Detalles de Obligación</b></h5>
                            <div>
                                <div class="alert status-alert" role="alert"></div>
                                <div class="details-card">
                                    <div class="info-section">
                                        <!-- Aquí puedes agregar detalles adicionales -->
                                        
                                    </div>
                                </div>
                                <hr>
                                <div class="details-card">
                                    <div class="section-header bg-light-grey">
                                        <i class="fas fa-file-upload"></i>
                                        <span>Agregar Archivos:</span>
                                    </div>
                                    <!-- Formulario de carga de archivos -->
                                    <form id="uploadForm" action="{{ route('archivos.subir') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="requisito_id" value="{{ $requisito->id }}">
                                        <input type="hidden" name="evidencia" value="{{ $evidencia->numero_evidencia }}">
                                        <input type="hidden" name="evidencian" value="{{ $evidencia->evidencia }}">
                                        <input type="hidden" name="fecha_limite_cumplimiento" value="{{ $detalle->fecha_limite_cumplimiento }}">
                                        <!-- Campos ocultos para el usuario y puesto -->
                                        <input type="hidden" name="usuario" value="{{ Auth::user()->name }}">
                                        <input type="hidden" name="puesto" value="{{ Auth::user()->puesto }}">
                                        <div class="form-group">
                                            <br>
                                            <label for="archivo">Seleccione un archivo</label>
                                            <input type="file" name="archivo" class="archivo" id="archivo" required>
                                        </div>
                                        @if (!Auth::user()->hasRole('invitado'))
                                        <!-- Mostrar botones para subir archivo y enviar correo si no es invitado -->
                                        <button type="button" class="btn btn-success" onclick="handleFileUpload('#uploadForm')">Subir Archivo</button>
                                        <button type="button" class="btn btn-success" onclick="ejecutarAccionConDatos()">Enviar correo</button>
                                    @else
                                        <!-- Mostrar mensaje si el usuario tiene el rol de invitado -->
                                        <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes adjuntar archivos.<b></p>
                                    @endif
                                    
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Sección 2: Archivos adjuntos -->
                        <div class="col-md-6">
                            <h5><b>Archivos adjuntos</b></h5>
                            <hr>
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered text-center">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre del Archivo</th>
                                            <th>Usuario</th>
                                            <th>Puesto</th>
                                            <th>Fecha de Subida</th>
                                            <th>Ver</th>
                                            <th>Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="archivosTableBody">
                                        <!-- Contenido de la tabla -->
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                            <!-- Botón "Marcar como cumplido" -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
@else
    <!-- Mensaje si $detalle no está definido -->
    <div class="text-center mt-4">
        <h5>Este usuario no tiene obligaciones registradas o el año no contiene obligaciones registradas.</h5>
    </div>
@endif

@if (Auth::user()->hasRole('invitado'))
    <p class="text-center text-muted" style="font-size: 1.1rem;"><b>Actualmente eres un usuario invitado y solo tienes acceso a estas obligaciones.<b></p>

@endif



@endsection


@section('css')
    <link rel="stylesheet" href="{{ asset('css/obligaciones/styles.css')}}"> 
@stop

@section('js')

<script>
    const approvedResultUrl = "{{ route('approved.resul') }}";
    const obtenerDetallesEvidenciaUrl = "{{ route('obtener.detalles') }}";
    const obtenerNotificacionesUrl = "{{ route('obtener.notificaciones') }}";
    const obtenerTablaNotificacionesUrl = "{{ route('obtener.tabla.notificaciones') }}";
    const obtenerDetalleEvidenciaUrl = "{{ route('obtener.detalle.evidencia') }}";
    const verificarArchivosUrl = "{{ route('obligaciones.verificarArchivos') }}";
    const enviarCorreoDatosEvidenciaUrl = '{{ route('enviar.correo.datos.evidencia') }}';
    const listarArchivosUrl = "{{ route('archivos.listar') }}";
    const storageUploadsUrl = "{{ asset('storage/uploads') }}";
    const cambiarEstadoRequisitoUrl = "{{ route('requisito.cambiarEstado') }}";
    const obtenerEstadoAprobadoUrl = "{{ route('approved.resul') }}";
    const actualizarPorcentajeUrl = "{{ route('actualizar.porcentaje') }}";
    const actualizarSumaPorcentajeUrl = "{{ route('actualizar.suma.porcentaje') }}";
    const eliminarArchivoUrl = "{{ route('archivos.eliminar') }}";
</script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const userRole = "{{ Auth::user()->roles->pluck('name')->first() }}";
    </script>    

<script src="{{ asset('js/gestion_obligaciones/obligaciones/obligaciones.js') }}"></script>    
    
@stop
