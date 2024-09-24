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

            axios.post(routes.approvedResult, { id: requisitoId })
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

// Función para obtener detalles de evidencia
function obtenerDetallesEvidencia(evidenciaId, requisitoId) {
    axios.post(routes.obtenerDetalles, { evidencia_id: evidenciaId })
    .then(function(response) {
        let fechasLimiteHtml = '';
        if (response.data.fechas_limite_cumplimiento && response.data.fechas_limite_cumplimiento.length > 0) {
            response.data.fechas_limite_cumplimiento.forEach(function(fecha) {
                fechasLimiteHtml += `<p><b>${fecha}</b></p>`;
            });
        } else {
            fechasLimiteHtml = '<p>No hay fechas límite de cumplimiento</p>';
        }

        document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5>${sanitizeInput(response.data.evidencia)}</h5>
            </div>
            <br>
            <div class="details-card">
                <div class="info-section">
                    <div class="logo-container" style="text-align: right;">
                        <img src="${window.logoPath}" alt="Logo" class="logo" style="width: 70px; height: auto;">
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
                    ${fechasLimiteHtml}
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

// Función para obtener notificaciones
function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios.post(routes.obtenerNotificaciones, { id_notificaciones: idNotificaciones })
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

// Función para obtener la tabla de notificaciones
function obtenerTablaNotificaciones(idNotificaciones, requisitoId) {
    axios.post(routes.obtenerTablaNotificaciones, { id_notificaciones: idNotificaciones })
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

// Función para manejar la subida de archivos
function handleFileUpload(formSelector) {
    const form = document.querySelector(formSelector);
    const formData = new FormData(form);

    let requisitoId, evidenciaId, fechaLimite;
    let archivoAdjunto = form.querySelector('input[type="file"]').files[0];

    // Validar si hay un archivo adjunto
    if (!archivoAdjunto) {
        Swal.fire('Error', 'Favor de verificar ya que no se tiene ningún archivo adjunto.', 'warning');
        return;
    }

    // Validar el tamaño del archivo (máximo 2MB en este ejemplo)
    const maxFileSize = 2 * 1024 * 1024; // 2MB
    if (archivoAdjunto.size > maxFileSize) {
        Swal.fire('Error', 'El archivo es demasiado grande. Comuníquese con el administrador del sistema.', 'warning');
        return;
    }

    // Validar el tipo de archivo
    const validFileTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'application/zip',
        'application/x-rar-compressed'
    ];
    if (!validFileTypes.includes(archivoAdjunto.type)) {
        Swal.fire('Error', 'Tipo de archivo no permitido. Los formatos permitidos son PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPEG, PNG, GIF, ZIP y RAR.', 'warning');
        return;
    }

    // Iterar sobre los datos del formulario y extraer los valores específicos
    for (var pair of formData.entries()) {
        console.log(`${pair[0]}: ${pair[1]}`);

        if (pair[0] === 'requisito_id') {
            requisitoId = pair[1];
        } else if (pair[0] === 'evidencia') {
            evidenciaId = pair[1];
        } else if (pair[0] === 'fecha_limite_cumplimiento') {
            fechaLimite = pair[1];
        }
    }

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
                form.reset();
            })
            .catch(function(error) {
                if (error.response) {
                    if (error.response.status === 413) {
                        Swal.fire('Error', 'El archivo es demasiado grande. Comuníquese con el administrador del sistema.', 'warning');
                    } else {
                        Swal.fire('Error', 'Favor de verificar ya que no se tiene ningún archivo adjunto.', 'warning');
                    }
                } else if (error.request) {
                    Swal.fire('Error', 'No se recibió respuesta del servidor.', 'error');
                } else {
                    Swal.fire('Error', 'Hubo un problema al preparar la solicitud.', 'error');
                }
                form.reset();
            });
        }
    });
}

// Función para enviar correo
function correoEnviar() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios.post(routes.enviarCorreoDatosEvidencia, datosRecuperados)
    .then(function(response) {
        Swal.fire('Éxito', 'El correo se envió correctamente.', 'success');
    })
    .catch(function(error) {
        console.error('Error al enviar el correo:', error);
        Swal.fire('Error', 'Hubo un problema al enviar el correo.', 'error');
    });
}

// Función para recargar los archivos
function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    if (!isValidId(requisitoId) || !isValidId(evidenciaId) || !isValidId(fechaLimite)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post(routes.listarArchivos, {
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
                    <td><button class="btn btn-sm btn-info btn-ver-archivo" data-url="${window.storagePath}/${sanitizeInput(archivo.nombre_archivo)}"><i class="fas fa-eye"></i></button></td>
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

// Función para eliminar un archivo
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
            axios.post(routes.eliminarArchivo, {
                id: archivoId
            })
            .then(function(response) {
                Swal.fire('Eliminado', 'El archivo ha sido eliminado.', 'success');
                cargarArchivos(requisitoId, evidenciaId, fechaLimite);
            })
            .catch(function(error) {
                console.error('Error al eliminar el archivo:', error);
                Swal.fire('Error', 'Hubo un problema al eliminar el archivo.', 'error');
            });
        }
    });
}

// Función para actualizar el estado de la obligación
function actualizarEstado(detalleId, requisitoId, responsable, numero_requisito) {
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
            axios.post(routes.cambiarEstadoRequisito, {
                id: detalleId
            })
            .then(function(response) {
                if (response.data.success) {
                    const aprobado = response.data.approved;
                    const elementoPrueba = document.querySelector('.status-alert');

                    if (aprobado) {
                        elementoPrueba.classList.remove('alert-danger');
                        elementoPrueba.classList.add('alert-success');
                        elementoPrueba.innerHTML = '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
                    } else {
                        elementoPrueba.classList.remove('alert-success');
                        elementoPrueba.classList.add('alert-danger');
                        elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta obligación volvió a su estatus inicial.';
                    }
                }
            })
            .catch(function(error) {
                console.error('Error al actualizar el estado:', error);
            });
        }
    });
}

// Función para abrir el modal de detalle de evidencia
function abrirModalDetalle(detalleId, requisitoId) {
    if (!detalleId || !requisitoId) {
        console.error('detalleId o requisitoId no están definidos');
        return;
    }

    $('#modalDetalleContent').modal('show');

    axios.post(routes.approvedResult, {
        id: detalleId
    })
    .then(function(response) {
        let aprobado = response.data.approved;
        const elementoPrueba = document.querySelector('.status-alert');

        if (aprobado) {
            elementoPrueba.classList.remove('alert-danger');
            elementoPrueba.classList.add('alert-success');
            elementoPrueba.innerHTML = '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
        } else {
            elementoPrueba.classList.remove('alert-success');
            elementoPrueba.classList.add('alert-danger');
            elementoPrueba.innerHTML = '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada.';
        }
    })
    .catch(function(error) {
        console.error('Error al obtener el estado approved:', error);
    });
}

// Función para obtener datos de la sección de información
function obtenerDatosInfoSection() {
    const infoSection = document.querySelector('#modal-detalles-obligacion');
    const datos = {};

    if (infoSection) {
        const evidenciaElement = infoSection.querySelector('p[style*="display: none;"] b');
        const nombreElement = infoSection.querySelector('p:nth-child(2) b');
        const periodicidadElement = infoSection.querySelector('.section-header + p');
        const responsableElement = infoSection.querySelector('.section-header + p + .section-header + p');
        const fechaLimiteElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p');
        const origenObligacionElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p + .section-header + p');
        const clausulaElement = infoSection.querySelector('.section-header + p + .section-header + p + .section-header + p + .section-header + p + .section-header + p');

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

// Función para ejecutar acciones con los datos recuperados
function ejecutarAccionConDatos() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios.post(routes.enviarCorreoDatosEvidencia, datosRecuperados)
    .then(function(response) {
        Swal.fire('Éxito', 'El correo se envió correctamente.', 'success');
    })
    .catch(function(error) {
        console.error('Error al enviar el correo:', error);
        Swal.fire('Error', 'Hubo un problema al enviar el correo.', 'error');
    });
}

// Función para actualizar el porcentaje de avance
function actualizarPorcentaje(detalleId) {
    if (!isValidId(detalleId)) {
        console.error('ID no válido');
        return;
    }

    axios.post(routes.actualizarPorcentaje, {
        id: sanitizeInput(detalleId)
    })
    .then(function(response) {
        if (response.data.success) {
            Swal.fire('Éxito', 'El porcentaje ha sido actualizado correctamente.', 'success');
        }
    })
    .catch(function(error) {
        console.error('Error al actualizar el porcentaje:', error);
        Swal.fire('Error', 'Hubo un problema al actualizar el porcentaje.', 'error');
    });
}

// Función para actualizar la suma del porcentaje
function actualizarPorcentajeSuma(detalleId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(numeroRequisito)) {
        console.error('IDs no válidos');
        return;
    }

    axios.post(routes.actualizarSumaPorcentaje, {
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

// Función para cambiar el color de estado de avance
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
    $('.modal').not(this).attr('inert', 'true');
});

$('#modalDetalleContent').on('hidden.bs.modal', function () {
    $('.modal').removeAttr('inert');
});




