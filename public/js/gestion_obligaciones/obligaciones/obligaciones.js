

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

            // Realizar la solicitud para obtener el estado "approved"
            axios.post(approvedResultUrl, { id: requisitoId })
            .then(function(response) {
                const aprobado = response.data.approved;
                const elementoPrueba = document.querySelector('.status-alert');
                // Aquí puedes agregar lógica adicional para manejar el resultado
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
    axios.post(obtenerDetallesEvidenciaUrl, { evidencia_id: evidenciaId })
    .then(function(response) {
        // Crear un único <ul> para todas las fechas
        let fechasLimiteHtml = '<ul style="list-style-type: disc; padding-left: 20px;">';
        if (response.data.fechas_limite_cumplimiento && response.data.fechas_limite_cumplimiento.length > 0) {
            response.data.fechas_limite_cumplimiento.forEach(function(fecha) {
                fechasLimiteHtml += `<li><b>${sanitizeInput(fecha)}</b></li>`;
            });
        } else {
            fechasLimiteHtml = '<p>No hay fechas límite de cumplimiento</p>';
        }
        fechasLimiteHtml += '</ul>'; // Cierra el único <ul> para todas las fechas

        // Insertar los detalles de la evidencia en el contenedor correspondiente
        document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5><b>${sanitizeInput(response.data.condicion)}</b></h5>
            </div>
            
            <div class="details-card mt-2">
                <div class="info-section">
                    <div class="logo-container" style="text-align: right;"></div>
                    
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar"></i>
                        <span>Periodicidad:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(response.data.periodicidad)}</b></li>
                    </ul>
                    
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-user"></i>
                        <span>Responsable:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(response.data.responsable)}</b></li>
                    </ul>
                    
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Fechas límite de cumplimiento:</span>
                    </div>
                    ${fechasLimiteHtml} <!-- Muestra la lista completa de fechas -->
                    
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-file-alt"></i>
                        <span>Origen de la obligación:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(response.data.origen_obligacion)}</b></li>
                    </ul>
                    
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-book"></i>
                        <span>Cláusula, condicionante, o artículo:</span>
                    </div>
                                                            ${
                                userRole === 'invitado' ? `
                                    <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                                ` : `
                                    <p style="text-align: justify;"><b>${sanitizeInput(response.data.clausula_condicionante_articulo)}</b></p>
                                `
                            }
                </div>
            </div>
        `;
    })
    .catch(function(error) {
        console.error('Error al obtener los detalles:', error);
    });
}


function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios.post(obtenerNotificacionesUrl, { id_notificaciones: idNotificaciones })
    .then(function(response) {
        let notificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-bell"></i>
                        <span>Notificación:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;"> <!-- Lista con viñetas -->
        `;
        
        if (response.data.length > 0) {
            response.data.forEach(function(nombre) {
                notificacionesHtml += `<li><b>${sanitizeInput(nombre)}</b></li>`;
            });
        } else {
            notificacionesHtml += '<li>No hay notificaciones</li>';
        }

        notificacionesHtml += '</ul></div></div>';
        document.getElementById("notificaciones-info-" + requisitoId).innerHTML = notificacionesHtml;
    })
    .catch(function(error) {
        console.error('Error al obtener las notificaciones:', error);
    });
}


function obtenerTablaNotificaciones(idNotificaciones, requisitoId) {
    axios.post(obtenerTablaNotificacionesUrl, { id_notificaciones: idNotificaciones })
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

        // Cerramos la tabla de notificaciones
        tablaNotificacionesHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        // Añadimos los botones de "Alertas por Correo Electrónico" debajo de la tabla
        tablaNotificacionesHtml += `
            <div class="details-card mt-2">
                <div class="section-header bg-light-grey">
                    <i class="fas fa-envelope"></i>
                    <span>Alertas por Correo Electrónico:</span>
                </div>
<div class="d-flex justify-content-around mt-4">
    <button class="btn btn-primary" style="background-color: #90ee90; color: black; border: 1px solid black;" onclick="enviarAlertaCorreo(30)">30 días</button>
    <button class="btn btn-primary" style="background-color: #ffff99; color: black; border: 1px solid black;" onclick="enviarAlertaCorreo(15)">15 días</button>
    <button class="btn btn-primary" style="background-color: #ffcc99; color: black; border: 1px solid black;" onclick="enviarAlertaCorreo(5)">5 días</button>
    <button class="btn btn-primary" style="background-color: #ff9999; color: black; border: 1px solid black;" onclick="enviarAlertaCorreo(2)">Inmediato (2 días)</button>
    <button class="btn btn-primary" style="background-color: #ff6666; color: black; border: 1px solid black;" onclick="enviarAlertaCorreo(1)">Inmediato (1 día)</button>
</div>
            </div>
        `;
          
        // Insertamos el contenido en el contenedor de la tabla de notificaciones
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
    
        axios.post(obtenerDetalleEvidenciaUrl, {
            evidencia_id: sanitizeInput(evidenciaId),
            detalle_id: sanitizeInput(detalleId),
            requisito_id: sanitizeInput(requisitoId)
        })
        .then(function(response) {
            const modalElement = document.getElementById("modalDetalleContent");
    
            if (modalElement) {
                const infoSection = modalElement.querySelector('.modal-body .info-section');
    
                if (infoSection) {
                    let content = `
                        <div class="header">
                            <h5><b>${sanitizeInput(response.data.condicion)}</b></h5>
                        </div>
                        <div class="details-card mt-2">
                            <div id="modal-detalles-obligacion" class="info-section">
                                <div class="logo-container" style="text-align: right;"></div>
                                <p style="display: none;"><b>${sanitizeInput(response.data.evidencia)}</b></p> 
                                <p style="display: none;"><b>${sanitizeInput(response.data.nombre)}</b></p>                         
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar"></i>
                                    <span>Periodicidad:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(response.data.periodicidad)}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-user"></i>
                                    <span>Responsable:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(response.data.responsable)}</b></li>
                                </ul>    
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Fechas límite de cumplimiento:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(response.data.fecha_limite_cumplimiento)}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Origen de la obligación:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(response.data.origen_obligacion)}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-book"></i>
                                    <span>Cláusula, condicionante, o artículo:</span>
                                </div>
                                                            ${
                                userRole === 'invitado' ? `
                                    <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                                ` : `
                                    <p style="text-align: justify;"><b>${sanitizeInput(response.data.clausula_condicionante_articulo)}</b></p>
                                `
                            }
                            </div>
                        </div>
                        <br>
                    `;
    
                    // Solo agregar el botón si el rol es 'admin'
                    if (userRole === 'admin') {
                        content += `
                            <button class="btn btn-secondary btnMarcarCumplido w-100" id="btnMarcarCumplido" data-requisito-id="${sanitizeInput(response.data.id)}" data-responsable="${sanitizeInput(response.data.responsable)}">
                                <i class=""></i> Cambiar estado de evidencia
                            </button>
                        `;
                    }
    
                    // Insertar el contenido en la sección de información
                    infoSection.innerHTML = content;
    
                    // Solo si el botón se ha renderizado, añadir la funcionalidad del evento click
                    const btnMarcarCumplido = document.getElementById("btnMarcarCumplido");
                    if (btnMarcarCumplido) {
                        btnMarcarCumplido.addEventListener('click', function() {
                            axios.post(verificarArchivosUrl, {
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
                    }
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
    

// Subir archivo
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

    // Validar el tamaño del archivo (máximo 20MB en este ejemplo)
    const maxFileSize = 20 * 1024 * 1024; // 20MB
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

    axios.post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
    .then(function(response) {
        Swal.fire('Éxito', 'El correo se envió correctamente.', 'success');
    })
    .catch(function(error) {
        console.error('Error al enviar el correo:', error);
        Swal.fire('Error', 'Hubo un problema al enviar el correo.', 'error');
    });
}


// Recargar los archivos para un requisito específico
function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    if (!isValidId(requisitoId) || !isValidId(evidenciaId) || !isValidId(fechaLimite)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post(listarArchivosUrl, {
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
                <td>${sanitizeInput(archivo.nombre_archivo.split('_').slice(1).join('_'))}</td>
                <td>${sanitizeInput(archivo.usuario)}</td>
                <td>${sanitizeInput(archivo.puesto)}</td>
                <td>${new Date(sanitizeInput(archivo.created_at)).toLocaleString()}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-info btn-ver-archivo" 
                        data-url="${storageUploadsUrl}/${sanitizeInput(archivo.nombre_archivo)}"
                        ${userRole === 'invitado' ? 'disabled' : ''}
                    >
                        <i class="fas fa-download"></i>
                    </button>
                </td>

                ${
                    userRole === 'admin' 
                        ? `<td><button class="btn btn-sm btn-danger btn-eliminar-archivo" onclick="eliminarArchivo(${sanitizeInput(archivo.id)}, '${sanitizeInput(requisitoId)}', '${sanitizeInput(evidenciaId)}', '${sanitizeInput(fechaLimite)}')"><i class="fas fa-trash-alt"></i></button></td>`
                        : `<td><button class="btn btn-sm btn-danger btn-eliminar-archivo" disabled><i class="fas fa-trash-alt"></i></button></td>`
                }
            </tr>`).join('')
        : '<tr><td colspan="8">No hay archivos adjuntos</td></tr>';
    
    document.getElementById('archivosTableBody').innerHTML = tableBody;
    
    

        document.querySelectorAll('.btn-ver-archivo').forEach(button => {
            button.addEventListener('click', function () {
                const fileUrl = this.dataset.url; // Obtener la URL del archivo
                const fileName = fileUrl.split('/').pop(); // Extraer el nombre del archivo
        
                // Crear un enlace temporal para forzar la descarga
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    })
    .catch(function(error) {
        console.error('Error al cargar los archivos:', error);
    });
}


// Marcado como revisado
function actualizarEstado(detalleId, requisitoId, responsable, numero_requisito) {

    console.log("el valor es: " + detalleId);
    
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

            actualizarPorcentaje(detalleId);
            actualizarPorcentajeSuma(detalleId, numero_requisito);
            // Si el usuario confirma, realiza la solicitud para cambiar el estado
            axios.post(cambiarEstadoRequisitoUrl, {
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
                    console.error('Error al actualizar el estado:', response.data.error);
                }
            })
            .catch(function(error) {
                console.error('Error en la solicitud:', error);
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
    axios.post(obtenerEstadoAprobadoUrl, {
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
            elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada o volvió a su estatus inicial.';
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
    
    axios.post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
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
    axios.post(cambiarEstadoRequisitoUrl, {
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

    axios.post(actualizarPorcentajeUrl, {
        id: sanitizeInput(detalleId)
    })
    .then(function(response) {
        if (response.data.success) {
            console.log('Porcentaje actualizado correctamente:', response.data);
            // Puedes agregar más lógica aquí si es necesario
        } else {
            throw new Error('Error al actualizar el porcentaje');
        }
    })
    .catch(function(error) {
        console.error('Error al actualizar el porcentaje:', error);
    });
}

function actualizarPorcentajeSuma(detalleId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(numeroRequisito)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post(actualizarSumaPorcentajeUrl, {
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
            axios.post(eliminarArchivoUrl, {
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


function enviarAlertaCorreo(diasRestantes) {
    console.log("Botón de alerta clicado para " + diasRestantes + " días");

    axios.post(enviarCorreoAlertaUrl, {
    dias_restantes: diasRestantes
})
.then(response => {
    console.log("Correo enviado:", response.data);
    Swal.fire({
        title: 'Correo Enviado',
        text: `Se ha enviado un correo para la alerta de ${diasRestantes} días.`,
        icon: 'success'
    });
})
.catch(error => {
    console.error("Error al enviar el correo:", error);
    Swal.fire({
        title: 'Error',
        text: 'Hubo un problema al enviar el correo.',
        icon: 'error'
    });
});

}


