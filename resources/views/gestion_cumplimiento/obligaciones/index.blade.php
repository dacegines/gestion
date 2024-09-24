
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
        @if(in_array($user->puesto, $puestosExcluidos))
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
                        @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                            <option value="{{ $yearOption }}" {{ (isset($year) && $year == $yearOption) ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm">Ver</button>
            </form>
        </div>              
        <div class="divider"></div>
        <button class="btn btn-success" onclick="location.reload();">Actualizar</button>
        <div class="row text-center justify-content-center" id="cajaContainer">

            
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
                            <!-- 
                            <br>
                            <div class="card-status">{{ $requisito->total_avance == 100 ? 'Completado' : 'Abierto' }}</div>
                            <div class="card-icons">
                                <i class="fas fa-info-circle"></i>
                                <i class="far fa-star"></i>
                            </div>
                            -->
                        </div>
                    </div>
                </div>
            @endforeach
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
                            <h5>Detalles de Obligación</h5>
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
                                        <button type="button" class="btn btn-success" onclick="handleFileUpload('#uploadForm')">Subir Archivo</button>
                                        <button type="button" class="btn btn-success" onclick="ejecutarAccionConDatos()">Enviar correo</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Sección 2: Archivos adjuntos -->
                        <div class="col-md-6">
                            <h5>Archivos adjuntos</h5>
                            <hr>
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered text-center">
                                    <thead>
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



@endsection


@section('css')
    <link rel="stylesheet" href="{{ asset('css/obligaciones/styles.css')}}"> 
@stop

@section('js')




<script>


    
    
        
    
    //Modal obligacion
    
// Función para validar que un ID sea un número entero válido
function isValidId(id) {
    return typeof id === 'string' && id.trim().length > 0;
}

// Función para sanitizar entradas de texto
function sanitizeInput(input) {
    const element = document.createElement('div');
    element.textContent = input;
    return element.innerHTML;
}

// Inicialización y control de comportamiento en modales
// Inicialización y control de comportamiento en modales
document.addEventListener('DOMContentLoaded', function() {
    // Unificar la lógica para manejar la apertura del segundo modal y cargar los detalles
    document.querySelectorAll('.custom-card').forEach(function(element) {
        element.addEventListener('click', function() {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;

            const firstModal = document.getElementById('modal' + requisitoId);
            if (firstModal) {
                $(firstModal).modal('hide');
            }

            obtenerDetallesEvidencia(evidenciaId, requisitoId);
            obtenerNotificaciones(idNotificaciones, requisitoId);
            obtenerTablaNotificaciones(idNotificaciones, requisitoId);

            axios.post("{{ route('approved.resul') }}", { id: requisitoId })
            .then(function(response) {
                const aprobado = response.data.approved;
                const elementoPrueba = document.querySelector('.status-alert');

            })
            .catch(function(error) {
                console.error('Error al obtener el estado approved:', error);
            });

            // Mostrar el modal y hacer los demás inertes
            $('#modalDetalleContent').modal('show');
            $('#modalDetalleContent').data('first-modal-id', 'modal' + requisitoId);

            // Desactivar otros modales y elementos fuera del modal activo
            $('.modal').not('#modalDetalleContent').attr('inert', 'true');
        });
    });

    $('#modalDetalleContent').on('hidden.bs.modal', function() {
        const firstModalId = $(this).data('first-modal-id');
        if (firstModalId) {
            const firstModal = document.getElementById(firstModalId);
            if (firstModal) {
                $(firstModal).find('form').trigger('reset');
                $(firstModal).find('.collapse').collapse('hide');
                $(firstModal).modal('show');
            }
        }

        // Reactivar otros modales y elementos
        $('.modal').removeAttr('inert');
    });

    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.collapse').collapse('hide');
        $(this).find('.info-container').empty();
    });
});


// Modal obligación - Carga de detalles y notificaciones
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.n_evidencia').forEach(function(element) {
        element.addEventListener('click', function() {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            
            // Validar los IDs antes de hacer cualquier otra cosa
            if (!isValidId(evidenciaId) || !isValidId(idNotificaciones) || !isValidId(requisitoId)) {
                Swal.fire('Error', 'Datos no válidos detectados.', 'error');
                return;
            }

            // Llamar a la función para obtener detalles de evidencia
            obtenerDetallesEvidencia(evidenciaId, requisitoId);

            // Llamar a la función para obtener notificaciones
            obtenerNotificaciones(idNotificaciones, requisitoId);

            // Obtener y mostrar la tabla de notificaciones
            obtenerTablaNotificaciones(idNotificaciones, requisitoId);            
        });
    });
});

function obtenerDetallesEvidencia(evidenciaId, requisitoId) {
    axios.post("{{ route('obtener.detalles') }}", { evidencia_id: evidenciaId })
    .then(function(response) {
        // Crear un string con todas las fechas
        let fechasLimiteHtml = '';
        if (response.data.fechas_limite_cumplimiento && response.data.fechas_limite_cumplimiento.length > 0) {
            response.data.fechas_limite_cumplimiento.forEach(function(fecha) {
                fechasLimiteHtml += `<p><b>${fecha}</b></p>`;
            });
        } else {
            fechasLimiteHtml = '<p>No hay fechas límite de cumplimiento</p>';
        }

        // Insertar los detalles de la evidencia en el contenedor correspondiente
        document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5>${sanitizeInput(response.data.evidencia)}</h5>
            </div>
            <br>
            <div class="details-card">
                <div class="info-section">
                    <div class="logo-container" style="text-align: right;">
                        <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo" style="width: 70px; height: auto;">
                    </div>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar"></i>
                        <span>Periodicidad:</span>
                    </div>
                    <p><b>${sanitizeInput(response.data.periodicidad)}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-user"></i>
                        <span>Responsable:</span>
                    </div>
                    <p><b>${sanitizeInput(response.data.responsable)}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Fechas límite de cumplimiento:</span>
                    </div>
                    ${fechasLimiteHtml}<!-- Aquí se muestran todas las fechas -->
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-file-alt"></i>
                        <span>Origen de la obligación:</span>
                    </div>
                    <p><b>${sanitizeInput(response.data.origen_obligacion)}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-book"></i>
                        <span>Cláusula, condicionante, o artículo:</span>
                    </div>
                    <p><b>${sanitizeInput(response.data.clausula_condicionante_articulo)}</b></p>
                </div>
            </div>
        `;
    })
    .catch(function(error) {
        console.error('Error al obtener los detalles:', error);
    });
}

function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios.post("{{ route('obtener.notificaciones') }}", { id_notificaciones: idNotificaciones })
    .then(function(response) {
        let notificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-bell"></i>
                        <span>Notificación:</span>
                    </div>
        `;
        
        if (response.data.length > 0) {
            response.data.forEach(function(nombre) {
                notificacionesHtml += `<p><b>${sanitizeInput(nombre)}</b></p>`;
            });
        } else {
            notificacionesHtml += '<p>No hay notificaciones</p>';
        }

        notificacionesHtml += '</div></div>';
        document.getElementById("notificaciones-info-" + requisitoId).innerHTML = notificacionesHtml;
    })
    .catch(function(error) {
        console.error('Error al obtener las notificaciones:', error);
    });
}

function obtenerTablaNotificaciones(idNotificaciones, requisitoId) {
    axios.post("{{ route('obtener.tabla.notificaciones') }}", { id_notificaciones: idNotificaciones })
    .then(function(response) {
        let tablaNotificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-table"></i>
                        <span>Tabla de Notificaciones:</span>
                    </div>
                    <div class="table-responsive">
                        <table class="styled-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Notificación</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        if (response.data.length > 0) {
            response.data.forEach(function(notificacion) {
                tablaNotificacionesHtml += `
                    <tr>
                        <td style="text-align: center;"><b>${sanitizeInput(notificacion.nombre)}</b></td>
                        <td style="text-align: center;"><b>${sanitizeInput(notificacion.tipo)}</b></td>
                        <td ${notificacion.estilo} style="text-align: center;"><b>${sanitizeInput(notificacion.dias)}</b></td>
                    </tr>
                `;
            });
        } else {
            tablaNotificacionesHtml += '<tr><td colspan="3" style="text-align: center;">No hay notificaciones</td></tr>';
        }

        tablaNotificacionesHtml += '</tbody></table></div></div></div>';
        document.getElementById("tabla-notificaciones-info-" + requisitoId).innerHTML = tablaNotificacionesHtml;
    })
    .catch(function(error) {
        console.error('Error al obtener la tabla de notificaciones:', error);
    });
}

    //modal detalle
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.custom-card').forEach(function(element) {
            element.addEventListener('click', function() {
                const detalleId = this.dataset.detalleId;
                const evidenciaId = this.dataset.evidenciaId;
                const requisitoId = this.dataset.requisitoId;
                const numeroRequisito = this.dataset.numeroRequisito; // Recuperar el numero_requisito
                const fechaLimiteCumplimiento = this.dataset.fechaLimiteCumplimiento;
                
                cargarArchivos(requisitoId, evidenciaId, fechaLimiteCumplimiento)
    
                // Llamar a la función para cargar los detalles
                cargarDetalleEvidencia(detalleId, evidenciaId, requisitoId, numeroRequisito);
            });
        });
    });
    
    function cargarDetalleEvidencia(detalleId, evidenciaId, requisitoId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(evidenciaId) || !isValidId(requisitoId)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post("{{ route('obtener.detalle.evidencia') }}", {
        evidencia_id: sanitizeInput(evidenciaId),
        detalle_id: sanitizeInput(detalleId),
        requisito_id: sanitizeInput(requisitoId)
    })
    .then(function(response) {
        const modalElement = document.getElementById("modalDetalleContent");

        if (modalElement) {
            const infoSection = modalElement.querySelector('.modal-body .info-section');

            if (infoSection) {
                infoSection.innerHTML = `
                    <div class="header">
                        <h5>${sanitizeInput(response.data.evidencia)}</h5>
                    </div>
                    <br>                    
                    <div class="details-card">
                        <div id="modal-detalles-obligacion" class="info-section">
                            <div class="logo-container" style="text-align: right;">
                                <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo" style="width: 70px; height: auto;">
                            </div>
                            <p style="display: none;"><b>${sanitizeInput(response.data.evidencia)}</b></p> 
                            <p style="display: none;"><b>${sanitizeInput(response.data.nombre)}</b></p>                         
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar"></i>
                                <span>Periodicidad:</span>
                            </div>
                            <p><b>${sanitizeInput(response.data.periodicidad)}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-user"></i>
                                <span>Responsable:</span>
                            </div>
                            <p><b>${sanitizeInput(response.data.responsable)}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Fechas límite de cumplimiento:</span>
                            </div>
                            <p><b>${sanitizeInput(response.data.fecha_limite_cumplimiento)}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-file-alt"></i>
                                <span>Origen de la obligación:</span>
                            </div>
                            <p><b>${sanitizeInput(response.data.origen_obligacion)}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-book"></i>
                                <span>Cláusula, condicionante, o artículo:</span>
                            </div>
                            <p><b>${sanitizeInput(response.data.clausula_condicionante_articulo)}</b></p>
                        </div>
                    </div>
                    <br>
                    <button class="btn btn-secondary btnMarcarCumplido" id="btnMarcarCumplido" data-requisito-id="${sanitizeInput(response.data.id)}" data-responsable="${sanitizeInput(response.data.responsable)}">
                        <i class=""></i> Cambiar estado de evidencia
                    </button>
                `;

                const btnMarcarCumplido = document.getElementById("btnMarcarCumplido");
                btnMarcarCumplido.addEventListener('click', function() {
                    axios.post("{{ route('obligaciones.verificarArchivos') }}", {
                        requisito_id: sanitizeInput(requisitoId),
                        fecha_limite_cumplimiento: sanitizeInput(response.data.fecha_limite_cumplimiento),
                        nombre_archivo: sanitizeInput(response.data.nombre_archivo)
                    })
                    .then(function (verifyResponse) {
                        if (verifyResponse.data.conteo === 0) {
                            Swal.fire({
                                title: "¡No hay archivos adjuntos para esta evidencia!",
                                text: "Para poder cambiar el estatus de la evidencia se requiere mínimo un archivo adjunto.",
                                icon: "error"
                            });
                        } else {
                            actualizarEstado(detalleId, requisitoId, sanitizeInput(response.data.responsable), sanitizeInput(numeroRequisito));
                        }
                    })
                    .catch(function (error) {
                        console.error('Error al verificar los archivos:', error);
                    });
                });
            } else {
                console.error('No se encontró la sección de información en el modal');
            }
        } else {
            console.error('No se encontró el modal con ID modalDetalle' + sanitizeInput(detalleId));
        }
    })
    .catch(function(error) {
        console.error('Error al obtener los detalles:', error);
    });
}
    
    
    //subir archivo
    
    document.querySelectorAll('.custom-card').forEach(function(element) {
        element.addEventListener('click', function() {
            const requisitoId = this.dataset.requisitoId;
            const evidenciaId = this.dataset.evidenciaId;
            const fechaLimite = this.dataset.fechaLimiteCumplimiento;
    
    
            // Asignar los valores al formulario
            document.querySelector('#uploadForm input[name="requisito_id"]').value = requisitoId;
            document.querySelector('#uploadForm input[name="evidencia"]').value = evidenciaId;
            document.querySelector('#uploadForm input[name="fecha_limite_cumplimiento"]').value = fechaLimite;
        });
    });
    
    
    function handleFileUpload(formSelector) {
    const form = document.querySelector(formSelector);
    const formData = new FormData(form);

    // Variables para almacenar los valores que necesitamos
    let requisitoId, evidenciaId, fechaLimite;
    let archivoAdjunto = form.querySelector('input[type="file"]').files[0];

    // Validar si hay un archivo adjunto
    if (!archivoAdjunto) {
        Swal.fire('Error', 'Favor de verificar ya que no se tiene ningún archivo adjunto.', 'warning');
        return; // Detener la ejecución si no hay archivo adjunto
    }

    // Validar el tamaño del archivo (máximo 2MB en este ejemplo)
    const maxFileSize = 2 * 1024 * 1024; // 2MB
    if (archivoAdjunto.size > maxFileSize) {
        Swal.fire('Error', 'El archivo es demasiado grande. Comuníquese con el administrador del sistema.', 'warning');
        return; // Detener la ejecución si el archivo es demasiado grande
    }

    // Validar el tipo de archivo
    const validFileTypes = [
        'application/pdf',               // PDF
        'image/jpeg',                    // JPEG
        'image/png',                     // PNG
        'image/gif',                     // GIF
        'application/msword',            // DOC
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
        'application/vnd.ms-excel',      // XLS
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
        'application/vnd.ms-powerpoint', // PPT
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX
        'text/plain',                    // TXT
        'application/zip',               // ZIP
        'application/x-rar-compressed'   // RAR
    ];
    if (!validFileTypes.includes(archivoAdjunto.type)) {
        Swal.fire('Error', 'Tipo de archivo no permitido. Los formatos permitidos son PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPEG, PNG, GIF, ZIP y RAR.', 'warning');
        return; // Detener la ejecución si el tipo de archivo no es permitido
    }

    // Iterar sobre los datos del formulario y extraer los valores específicos
    for (var pair of formData.entries()) {
        console.log(`${pair[0]}: ${pair[1]}`);

        // Comparar las claves y asignar los valores a las variables correspondientes
        if (pair[0] === 'requisito_id') {
            requisitoId = pair[1];
        } else if (pair[0] === 'evidencia') {
            evidenciaId = pair[1];
        } else if (pair[0] === 'fecha_limite_cumplimiento') {
            fechaLimite = pair[1];
        }
    }

    // Mostrar los valores específicos en un solo console.log

    Swal.fire({
        title: '¿Estás seguro?',
        html: 'Al subir este archivo se enviará una notificación vía correo a: <br>Director Jurídico, <br>Gerente Jurídico, <br>Jefa de Cumplimiento.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, subir archivo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {

            // Si el usuario confirma, se procede con la subida del archivo
            axios.post(form.action, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(function(response) {
                console.log('Archivo subido exitosamente:', response.data);
                Swal.fire('Éxito', 'El archivo se subió correctamente.', 'success');
                correoEnviar();
                cargarArchivos(requisitoId, evidenciaId, fechaLimite);
                // Limpiar el formulario después de una subida exitosa
                form.reset();
            })
            .catch(function(error) {
                if (error.response) {
                    if (error.response.status === 413) {
                        // Archivo demasiado grande
                        Swal.fire('Error', 'El archivo es demasiado grande. Comuníquese con el administrador del sistema.', 'warning');
                    } else {
                        Swal.fire('Error', 'Favor de verificar ya que no se tiene ningún archivo adjunto.', 'warning');
                    }
                } else if (error.request) {
                    Swal.fire('Error', 'No se recibió respuesta del servidor.', 'error');
                } else {
                    Swal.fire('Error', 'Hubo un problema al preparar la solicitud.', 'error');
                }

                // Limpiar el formulario incluso si hay un error
                form.reset();
            });
        }
    });
}



    
    
    function correoEnviar() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios.post('{{ route('enviar.correo.datos.evidencia') }}', datosRecuperados)
    .then(function(response) {
        Swal.fire('Éxito', 'El correo se envió correctamente.', 'success');
    })
    .catch(function(error) {
        console.error('Error al enviar el correo:', error);
        Swal.fire('Error', 'Hubo un problema al enviar el correo.', 'error');
    });
}
    
    
    //recuperar datos de archivos
    // Recargar los archivos para un requisito específico
    function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    if (!isValidId(requisitoId) || !isValidId(evidenciaId) || !isValidId(fechaLimite)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post("{{ route('archivos.listar') }}", {
        requisito_id: sanitizeInput(requisitoId),
        evidencia_id: sanitizeInput(evidenciaId),
        fecha_limite: sanitizeInput(fechaLimite)
    })
    .then(function(response) {
        const archivos = response.data.archivos;
        let tableBody = archivos.length > 0 
            ? archivos.map((archivo, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${sanitizeInput(archivo.nombre_archivo)}</td>
                    <td>${sanitizeInput(archivo.usuario)}</td>
                    <td>${sanitizeInput(archivo.puesto)}</td>
                    <td>${new Date(sanitizeInput(archivo.created_at)).toLocaleString()}</td>
                    <td><button class="btn btn-sm btn-info btn-ver-archivo" data-url="{{ asset('storage/uploads') }}/${sanitizeInput(archivo.nombre_archivo)}"><i class="fas fa-eye"></i></button></td>
                    <td><button class="btn btn-sm btn-danger btn-eliminar-archivo" onclick="eliminarArchivo(${sanitizeInput(archivo.id)}, '${sanitizeInput(requisitoId)}', '${sanitizeInput(evidenciaId)}', '${sanitizeInput(fechaLimite)}')"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`).join('')
            : '<tr><td colspan="8">No hay archivos adjuntos</td></tr>';

        document.getElementById('archivosTableBody').innerHTML = tableBody;

        document.querySelectorAll('.btn-ver-archivo').forEach(function(button) {
            button.addEventListener('click', function() {
                window.open(sanitizeInput(this.dataset.url), '_blank');
            });
        });
    })
    .catch(function(error) {
        console.error('Error al cargar los archivos:', error);
    });
}
    
    
    // marcado como revizado 
    function actualizarEstado(detalleId, requisitoId, responsable, numero_requisito) {

        console.log("el valor es: "+detalleId)
        
        // Mostrar alerta de confirmación con SweetAlert2
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Está a punto de modificar el estatus de esta obligación. Se notificará al responsable correspondiente por correo electrónico.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar estatus',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "El estado de la obligación ha sido cambiado",
                    showConfirmButton: false,
                    timer: 3000
                });
    
                actualizarPorcentaje(detalleId)
                actualizarPorcentajeSuma(detalleId, numero_requisito);
                // Si el usuario confirma, realiza la solicitud para cambiar el estado
                axios.post("{{ route('requisito.cambiarEstado') }}", {
                    id: detalleId
                })
                .then(function(response) {
                    if (response.data.success) {
                        console.log('Nuevo estado del requisito:', response.data.approved);
    
                        const button = document.querySelector(`.btnMarcarCumplido[data-requisito-id="${requisitoId}"]`);
    

    
                        // Aquí ejecutamos la lógica para actualizar el contenido del elemento con la clase 'status-alert'
                        const aprobado = response.data.approved;
                        const elementoPrueba = document.querySelector('.status-alert');
    
                        if (aprobado) {
                            // Establecer la clase de éxito y el mensaje correspondiente
                            elementoPrueba.classList.remove('alert-danger');
                            elementoPrueba.classList.add('alert-success');
                            elementoPrueba.innerHTML = '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
                        } else {
                            // Establecer la clase de error y el mensaje correspondiente
                            elementoPrueba.classList.remove('alert-success');
                            elementoPrueba.classList.add('alert-danger');
                            elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta obligación volvió a su estatus inicial.';
                        }
    
                    } else {
                        //console.error('Error al actualizar el estado:', response.data.error);
                    }
                })
                .catch(function(error) {
                    //console.error('Error en la solicitud:', error);
                });
            }
        });
    }
function abrirModalDetalle(detalleId, requisitoId) {
    // Asegúrate de que los valores no están vacíos
    if (!detalleId || !requisitoId) {
        console.error('detalleId o requisitoId no están definidos');
        return;
    }

    // Ahora puedes usar estos valores para hacer tus solicitudes y abrir el modal
    $('#modalDetalleContent').modal('show'); // Abrir el modal

    // Hacer la solicitud al servidor para obtener el estado 'approved'
    axios.post("{{ route('approved.resul') }}", {
        id: detalleId
    })
    .then(function(response) {
        let aprobado = response.data.approved;
        const elementoPrueba = document.querySelector('.status-alert');

        if (aprobado) {
            // Establecer la clase de éxito y el mensaje correspondiente
            elementoPrueba.classList.remove('alert-danger');
            elementoPrueba.classList.add('alert-success');
            elementoPrueba.innerHTML = '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
        } else {
            // Establecer la clase de error y el mensaje correspondiente
            elementoPrueba.classList.remove('alert-success');
            elementoPrueba.classList.add('alert-danger');
            elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada.';
        }
    })
    .catch(function(error) {
        console.error('Error al obtener el estado approved:', error);
    });
}




    
    
    function obtenerDatosInfoSection() {
        const infoSection = document.querySelector('#modal-detalles-obligacion');
        const datos = {};
    
        if (infoSection) {
            
            const evidenciaElement = infoSection.querySelector('p[style*="display: none;"] b');
            const nombreElement = infoSection.querySelector('p:nth-child(2) b'); // Recupera el nombre
            const periodicidadElement = infoSection.querySelector('.section-header + p');
            const responsableElement = infoSection.querySelector('.section-header + p + .section-header + p');
            const fechaLimiteElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p');
            const origenObligacionElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p + .section-header + p');
            const clausulaElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p + .section-header + p + .section-header + p');
    
            // Asignar valores al objeto datos
            
            datos.evidencia = evidenciaElement ? evidenciaElement.textContent.trim() : '';
            datos.nombre = nombreElement ? nombreElement.textContent.trim() : '';
            datos.periodicidad = periodicidadElement ? periodicidadElement.textContent.trim() : '';
            datos.responsable = responsableElement ? responsableElement.textContent.trim() : '';
            datos.fecha_limite_cumplimiento = fechaLimiteElement ? fechaLimiteElement.textContent.trim() : '';
            datos.origen_obligacion = origenObligacionElement ? origenObligacionElement.textContent.trim() : '';
            datos.clausula_condicionante_articulo = clausulaElement ? clausulaElement.textContent.trim() : '';
        } else {
            console.error('No se encontró la sección de información con la clase "info-section".');
        }
    
        return datos;
    }
    
    // Ejemplo de función que llama a `obtenerDatosInfoSection`
    function ejecutarAccionConDatos() {
        const datosRecuperados = obtenerDatosInfoSection();
        
        axios.post('{{ route('enviar.correo.datos.evidencia') }}', datosRecuperados)
        .then(function(response) {
            console.log('Correo enviado correctamente:', response.data);
            Swal.fire('Éxito', 'El correo se envió correctamente.', 'success');
        })
        .catch(function(error) {
            console.error('Error al enviar el correo:', error);
            Swal.fire('Error', 'Hubo un problema al enviar el correo.', 'error');
        });
    }
    
    function cambiarEstadoEvidencia(requisitoId, evidenciaId) {
        // Cambiar el estado de la evidencia
        axios.post("{{ route('requisito.cambiarEstado') }}", {
            id: requisitoId
        })
        .then(function(response) {
            if (response.data.success) {
                Swal.fire('Éxito', 'El estado de la evidencia ha sido cambiado.', 'success');
            } else {
                throw new Error('Error al cambiar el estado');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Hubo un problema durante el proceso.', 'error');
        });
    }
    
    function actualizarPorcentaje(detalleId) {
    if (!isValidId(detalleId)) {
        console.error('ID no válido');
        return;
    }

    axios.post('{{ route('actualizar.porcentaje') }}', {
        id: sanitizeInput(detalleId)
    })

}
    
function actualizarPorcentajeSuma(detalleId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(numeroRequisito)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post("{{ route('actualizar.suma.porcentaje') }}", {
        requisito_id: sanitizeInput(detalleId),
        numero_requisito: sanitizeInput(numeroRequisito)
    })
    .then(function(response) {
        console.log('Número de registros encontrados:', response.data.conteo);
        console.log('Porcentaje por cada registro:', response.data.porcentaje_por_registro);
    })
    .catch(function(error) {
        console.error('Error al contar los registros:', error);
    });
}
    
    // Función para eliminar el archivo cuando se hace clic en el botón
    function eliminarArchivo(archivoId, requisitoId, evidenciaId, fechaLimite) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Este archivo se eliminará permanentemente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                
                 // Llama a la función para recargar la lista de archivos
                axios.post("{{ route('archivos.eliminar') }}", {
                    id: archivoId
                })
                .then(function (response) {
                    Swal.fire('Eliminado', 'El archivo ha sido eliminado.', 'success');
                    // Recargar la lista de archivos o actualizar la vista según sea necesario
    
                    cargarArchivos(requisitoId, evidenciaId, fechaLimite);
                })
                .catch(function (error) {
                    console.error('Error al eliminar el archivo:', error);
                    Swal.fire('Error', 'Hubo un problema al eliminar el archivo.', 'error');
                });
                
            }
        });
    }
    
    
    // Cambiar color de estado de avance
document.addEventListener('DOMContentLoaded', function() {
    const statusIndicators = document.querySelectorAll('.status-indicator');

    statusIndicators.forEach(function(indicator) {
        if (indicator.textContent.trim() === 'Completo') {
            indicator.style.backgroundColor = 'green';
        }
    });

    const avances = document.querySelectorAll('.avance-obligacion');

    avances.forEach(function(avance) {
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

    
$('#modalDetalleContent').on('show.bs.modal', function () {
    // Añadir 'inert' a todos los demás modales y elementos que no deberían ser interactivos
    $('.modal').not(this).attr('inert', 'true');

    // Opcionalmente, puedes agregar 'inert' a otros elementos que no deberían ser accesibles
    // Ejemplo:
    // $('header, footer, .sidebar').attr('inert', 'true');
});

$('#modalDetalleContent').on('hidden.bs.modal', function () {
    // Quitar 'inert' cuando el modal se cierra para restaurar la interactividad
    $('.modal').removeAttr('inert');

    // Si agregaste 'inert' a otros elementos, quítalo también
    // Ejemplo:
    // $('header, footer, .sidebar').removeAttr('inert');
});

    
    </script>
    



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
@stop
