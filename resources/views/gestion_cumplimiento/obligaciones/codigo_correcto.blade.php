
@extends('adminlte::page')

@section('title', 'Obligaciones')

@section('content')
<br>
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

<div class="card-header bg-success text-white text-center">
    <h3 class="">
        @if(in_array($user->puesto, $puestosExcluidos))
            Obligaciones
        @else
            Obligaciones - {{ $user->puesto }}
        @endif
    </h3>
</div>

    <div class="card-body">
        <div class="divider"></div>
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
                <!-- Botón para abrir el modal -->
                <div class="custom-card text-center mb-3" 
                     data-toggle="modal" 
                     data-target="#modalDetalleContent" 
                     data-detalle-id="{{ $detalle->id }}" 
                     data-evidencia-id="{{ $evidencia->numero_evidencia }}" 
                     data-requisito-id="{{ $requisito->id }}" 
                     data-fecha-limite-cumplimiento="{{ $detalle->fecha_limite_cumplimiento }}"
                     data-numero-requisito="{{ $requisito->numero_requisito }}" >
                     
                     <span class="load-archivos" 
                        data-requisito-id="{{ $requisito->id }}" 
                        data-evidencia-id="{{ $evidencia->numero_evidencia }}">
                        {{ \Carbon\Carbon::parse($detalle->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
                    </span>
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
        
        <!-- Este contenedor ahora tiene un ID único basado en $requisito->id -->
    </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach


<!-- Modales para cada detalle -->

            <div class="modal fade modalDetalle" id="modalDetalleContent" tabindex="-1" aria-labelledby="modalDetalleLabel{{ $detalle->id }}" aria-hidden="true">
                <div class="modal-dialog modal-xl">
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

@endsection


@section('css')
    <link rel="stylesheet" href="{{ asset('css/obligaciones/styles.css')}}"> 
@stop

@section('js')

<script>



// Función para crear un input o textarea
function createInputElement(type, name, value) {
    let inputElement;
    if (type === 'textarea') {
        inputElement = document.createElement('textarea');
        inputElement.rows = 4;
    } else {
        inputElement = document.createElement('input');
        inputElement.type = type;
    }
    inputElement.name = name;
    inputElement.id = 'input' + capitalizeFirstLetter(name);
    inputElement.value = value || ''; // Asignar el valor del campo
    inputElement.classList.add('form-control');
    return inputElement;
}

// Función para crear un elemento select
function createSelectElement(name, options) {
    const selectElement = document.createElement('select');
    selectElement.name = name;
    selectElement.id = 'input' + capitalizeFirstLetter(name);
    selectElement.classList.add('form-control');

    // Agregar las opciones al select
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.label;
        selectElement.appendChild(optionElement);
    });

    return selectElement;
}

// Función para capitalizar la primera letra de una cadena
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Función para crear un grupo de formulario con label e input
function createFormGroup(labelText, inputElement) {
    const formGroup = document.createElement('div');
    formGroup.classList.add('form-group');

    const label = document.createElement('label');
    label.textContent = labelText;
    formGroup.appendChild(label);

    formGroup.appendChild(inputElement);

    return formGroup;
}



    

//Modal obligacion

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.n_evidencia').forEach(function(element) {
        element.addEventListener('click', function() {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            
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
    axios.post("{{ route('obtener.detalles') }}", {
        evidencia_id: evidenciaId
    })
    .then(function(response) {
        //console.log('Detalles de evidencia:', response.data);

        // Crear un string con todas las fechas
        let fechasLimiteHtml = '';
        if (response.data.fechas_limite_cumplimiento && response.data.fechas_limite_cumplimiento.length > 0) {
            response.data.fechas_limite_cumplimiento.forEach(function(fecha) {
                fechasLimiteHtml += `<p><b>${fecha}</b></p>`;
            });
        } else {
            fechasLimiteHtml = '<p>No hay fechas límite de cumplimiento</p>';
        }

        // Ahora lo insertas en tu HTML
        document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5>${response.data.evidencia}</h5>
            </div>
            <br>
            <div class="details-card">
                <div class="info-section">
                    <div class="logo-container" style="text-align: right;">
                        <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo" style="width: 100px; height: auto;">
                    </div>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar"></i>
                        <span>Periodicidad:</span>
                    </div>
                    <p><b>${response.data.periodicidad}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-user"></i>
                        <span>Responsable:</span>
                    </div>
                    <p><b>${response.data.responsable}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Fechas límite de cumplimiento:</span>
                    </div>
                    ${fechasLimiteHtml}  <!-- Aquí se muestran todas las fechas -->
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-file-alt"></i>
                        <span>Origen de la obligación:</span>
                    </div>
                    <p><b>${response.data.origen_obligacion}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-book"></i>
                        <span>Cláusula, condicionante, o artículo:</span>
                    </div>
                    <p><b>${response.data.clausula_condicionante_articulo}</b></p>
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-bell"></i>
                        <span>Notificaciones:</span>
                    </div>
                    <p id="imprime-${requisitoId}"><b></b></p> 
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-table"></i>
                        <span>Tabla de Notificaciones:</span>
                    </div>
                    <div id="tabla-notificaciones-${requisitoId}"></div>                                       
                </div>
            </div>
        `;
    })
    .catch(function(error) {
        console.error('Error al obtener los detalles:', error);
    });
}

function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios.post("{{ route('obtener.notificaciones') }}", {
        id_notificaciones: idNotificaciones
    })
    .then(function(response) {
        //console.log('Notificaciones obtenidas:', response.data);
        let notificacionesHtml = '';
        if (response.data.length > 0) {
            response.data.forEach(function(nombre) {
                notificacionesHtml += `<p><b>${nombre}</b></p>`;
            });
        } else {
            notificacionesHtml = '<p>No hay notificaciones</p>';
        }

        // Imprime las notificaciones en el elemento con id "imprime"
        document.getElementById("imprime-" + requisitoId).innerHTML = notificacionesHtml;
    })
    .catch(function(error) {
        console.error('Error al obtener las notificaciones:', error);
    });
}

function obtenerTablaNotificaciones(idNotificaciones, requisitoId) {
    axios.post("{{ route('obtener.tabla.notificaciones') }}", {
        id_notificaciones: idNotificaciones
    })
    .then(function(response) {
        //console.log('Tabla de notificaciones obtenida:', response.data);
        
        let tablaNotificacionesHtml = `
            <div class="table-responsive mt-2">
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
                        <td style="text-align: center;"><b>${notificacion.nombre}</b></td>
                        <td style="text-align: center;"><b>${notificacion.tipo}</b></td>
                        <td ${notificacion.estilo} style="text-align: center;"><b>${notificacion.dias}</b></td>
                    </tr>
                `;
            });
        } else {
            tablaNotificacionesHtml += `
                <tr>
                    <td colspan="3" style="text-align: center;">No hay notificaciones</td>
                </tr>
            `;
        }

        tablaNotificacionesHtml += `
                    </tbody>
                </table>
            </div>
        `;

        // Imprimir la tabla de notificaciones en el contenedor adecuado
        document.getElementById("tabla-notificaciones-" + requisitoId).innerHTML = tablaNotificacionesHtml;
    })
    .catch(function(error) {
        //console.error('Error al obtener la tabla de notificaciones:', error);
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
    axios.post("{{ route('obtener.detalle.evidencia') }}", {
        evidencia_id: evidenciaId,
        detalle_id: detalleId,
        requisito_id: requisitoId
    })
    .then(function(response) {
        const modalElement = document.getElementById("modalDetalleContent");

        if (modalElement) {
            const infoSection = modalElement.querySelector('.modal-body .info-section');

            if (infoSection) {
                // Limpiar el contenido anterior
                infoSection.innerHTML = '';

                // Insertar los nuevos datos
                infoSection.innerHTML = `
                    <div class="header">
                        <h5>${response.data.evidencia}</h5>
                    </div>
                    <br>                    

                    <div class="details-card">
                        <div id="modal-detalles-obligacion" class="info-section">
                            <div class="logo-container" style="text-align: right;">
                                <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo" style="width: 100px; height: auto;">
                            </div>
                              
                            <p style="display: none;"><b>${response.data.evidencia}</b></p> 
                            <p style="display: none;"><b>${response.data.nombre}</b></p>                         
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar"></i>
                                <span>Periodicidad:</span>
                            </div>
                            <p><b>${response.data.periodicidad}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-user"></i>
                                <span>Responsable:</span>
                            </div>
                            <p><b>${response.data.responsable}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Fechas límite de cumplimiento:</span>
                            </div>
                            <p><b>${response.data.fecha_limite_cumplimiento}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-file-alt"></i>
                                <span>Origen de la obligación:</span>
                            </div>
                            <p><b>${response.data.origen_obligacion}</b></p>
                            <div class="section-header bg-light-grey">
                                <i class="fas fa-book"></i>
                                <span>Cláusula, condicionante, o artículo:</span>
                            </div>
                            <p><b>${response.data.clausula_condicionante_articulo}</b></p>
                        </div>
                        
                    </div>
                    <br>
                    @role('superuser|admin')
                        <button class="btn btn-secondary btnMarcarCumplido" id="btnMarcarCumplido" data-requisito-id="${response.data.id}" data-responsable="${response.data.responsable}">
                            
                            <i class=""></i> Cambiar estado de evidencia
                        </button>   
                    @endrole     
                `;

                // Añadir evento click al botón después de insertarlo en el DOM
                const btnMarcarCumplido = document.getElementById("btnMarcarCumplido");
                btnMarcarCumplido.addEventListener('click', function() {
                    console.log(response.data.nombre_archivo);
                    if (!response.data.nombre_archivo) {  // Verificamos si es null o está vacío
                        Swal.fire({
                        title: "The Internet?",
                        text: "That thing is still around?",
                        icon: "error"
                        });Swal.fire({
                        title: "¡No hay archivos adjuntos para esta evidencia!",
                        text: "Para poder cambiar el estatus de la evidencia se requiere mínimo un archivo adjunto.",
                        icon: "error"
                        });
                    } else {
                        const requisitoId = this.dataset.requisitoId;
                        const responsable = this.dataset.responsable;
                        const numero_requisito = numeroRequisito;

                        actualizarEstado(requisitoId, responsable, numero_requisito);
                        
                        
                    }
                    

                });
            } else {
                console.error('No se encontró la sección de información en el modal');
            }
        } else {
            console.error('No se encontró el modal con ID modalDetalle' + detalleId);
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
    correoEnviar();
    
    const form = document.querySelector(formSelector);
    const formData = new FormData(form);

    // Variables para almacenar los valores que necesitamos
    let requisitoId, evidenciaId, fechaLimite;

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
                
                cargarArchivos(requisitoId, evidenciaId, fechaLimite)
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
    // Crear un objeto FormData para enviar los datos del formulario
    let formData = new FormData(document.getElementById('uploadForm'));

    // Realizar la petición AJAX usando Axios para subir el archivo y enviar el correo
    axios.post('enviar.correo.datos.evidencia', formData)
    .then(function(response) {
        console.log('Archivo subido exitosamente:', response.data);
        Swal.fire('Éxito', 'El archivo se subió y se envió el correo correctamente.', 'success');
    })
    .catch(function(error) {
        console.error('Error al subir el archivo o enviar el correo:', error);
        Swal.fire('Error', 'Hubo un problema al subir el archivo o enviar el correo.', 'error');
    });
}


//recuperar datos de archivos
// Recargar los archivos para un requisito específico
function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    axios.post("{{ route('archivos.listar') }}", {
        requisito_id: requisitoId,
        evidencia_id: evidenciaId,
        fecha_limite: fechaLimite // Agregar fechaLimite como un parámetro en la solicitud
    })
    .then(function(response) {
        

        const archivos = response.data.archivos;
        let tableBody = '';

        if (archivos.length > 0) {
            archivos.forEach(function(archivo, index) {
                const createdAt = new Date(archivo.created_at);
                const formattedDate = createdAt.toLocaleDateString() + ' ' + createdAt.toLocaleTimeString();
                const archivoUrl = `{{ asset('storage/uploads') }}/${archivo.nombre_archivo}`;
                
                tableBody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${archivo.nombre_archivo}</td>
                        <td>${archivo.usuario}</td>
                        <td>${archivo.puesto}</td>
                        <td>${formattedDate}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-ver-archivo" data-url="${archivoUrl}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger btn-eliminar-archivo" onclick="eliminarArchivo(${archivo.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                        </td>
                    </tr>
                `;
            });
        } else {
            tableBody = '<tr><td colspan="8">No hay archivos adjuntos</td></tr>';
        }

        const tableBodyElement = document.getElementById('archivosTableBody');
        if (!tableBodyElement) {
            console.error('No se encontró el elemento con ID archivosTableBody');
            return;
        }

        tableBodyElement.innerHTML = tableBody;

        // Añadir eventos para la vista previa del archivo
        document.querySelectorAll('.btn-ver-archivo').forEach(function(button) {
            button.addEventListener('click', function() {
                const url = this.dataset.url;
                console.log('Abriendo URL:', url); // Esto te ayudará a verificar qué URL se está abriendo
                window.open(url, '_blank'); // Abre el archivo en una nueva pestaña
            });
        });


    })
    .catch(function(error) {
        console.error('Error al cargar los archivos:', error);
    });
}


// marcado como revizado 
function actualizarEstado(requisitoId, responsable, numero_requisito) {
    
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
                title: "El estado de la evidencia ha sido cambiado",
                showConfirmButton: false,
                timer: 2000
            });

            actualizarPorcentaje(requisitoId)
            actualizarPorcentajeSuma(requisitoId, numero_requisito);
            // Si el usuario confirma, realiza la solicitud para cambiar el estado
            axios.post("{{ route('requisito.cambiarEstado') }}", {
                id: requisitoId
            })
            .then(function(response) {
                if (response.data.success) {
                    console.log('Nuevo estado del requisito:', response.data.approved);

                    const button = document.querySelector(`.btnMarcarCumplido[data-requisito-id="${requisitoId}"]`);

                    // Actualizar el botón según el nuevo estado
                    if (response.data.approved) {
                        button.classList.add('btn-secondary');
                        button.innerHTML = '<i class=""></i> Cambiar de estado de evidencia';
                    } else {
                        button.classList.add('btn-secondary');
                        button.innerHTML = '<i class=""></i> Cambiar de estado de evidencia';
                    }

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
document.addEventListener('DOMContentLoaded', function() {
    // Escuchar el evento de apertura del modal
    $('#modalDetalleContent').on('shown.bs.modal', function(event) {
        
        

        // Obtener el requisitoId del botón que disparó la apertura del modal
        const button = $(event.relatedTarget); // Botón que disparó el modal
        const requisitoId = button.data('detalle-id'); // Extraer el id del atributo data-requisito-id
        

        // Asegúrate de que requisitoId tenga un valor antes de continuar
        if (typeof requisitoId === 'undefined') {
            console.error('requisitoId no está definido');
            return;
        }

        // Hacer la solicitud al servidor para obtener el estado 'approved'
        axios.post("{{ route('approved.resul') }}", {
            id: requisitoId
        })
        .then(function(response) {
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
                elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada.';
            }
        })
        .catch(function(error) {
            console.error('Error al obtener el estado approved:', error);
        });
    });
});


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
    
    axios.post('/ruta-enviar-correo-datos-evidencia', datosRecuperados)
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

function actualizarPorcentaje(requisitoId) {
    axios.post('/actualizar-porcentaje', {
        id: requisitoId
    })
    .then(function(response) {
        if (response.data.success) {
            Swal.fire('Éxito', 'El porcentaje ha sido actualizado correctamente.', 'success');
        } else {
            throw new Error('Error al actualizar el porcentaje');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Hubo un problema al actualizar el porcentaje.', 'error');
    });
}

function actualizarPorcentajeSuma(requisitoId, numeroRequisito) {
    console.log("Número de requisito recibido:", numeroRequisito);

    // Realizar la solicitud al backend para contar los requisitos, obtener el porcentaje y actualizarlo
    axios.post("{{ route('actualizar.suma.porcentaje') }}", {
        requisito_id: requisitoId,
        numero_requisito: numeroRequisito
    })
    .then(function(response) {
        const conteo = response.data.conteo;
        const porcentajePorRegistro = response.data.porcentaje_por_registro;

        // Mostrar el conteo y el porcentaje por registro en la consola
        console.log('Número de registros encontrados:', conteo);
        console.log('Porcentaje por cada registro:', porcentajePorRegistro);

        // Opcional: Puedes hacer algo con el porcentaje o conteo en la interfaz de usuario
    })
    .catch(function(error) {
        console.error('Error al contar los registros:', error);
    });
}

// Función para eliminar el archivo cuando se hace clic en el botón
function eliminarArchivo(archivoId) {
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
            axios.post("{{ route('archivos.eliminar') }}", {
                id: archivoId
            })
            .then(function (response) {
                Swal.fire('Eliminado', 'El archivo ha sido eliminado.', 'success');
                // Recargar la lista de archivos o actualizar la vista según sea necesario
                cargarArchivos(); // Llama a la función para recargar la lista de archivos
            })
            .catch(function (error) {
                console.error('Error al eliminar el archivo:', error);
                Swal.fire('Error', 'Hubo un problema al eliminar el archivo.', 'error');
            });
        }
    });
}







</script>


    <script src="{{ asset('js/js/obligaciones/main.js')}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
@stop
