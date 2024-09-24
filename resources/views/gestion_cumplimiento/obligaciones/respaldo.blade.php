<script>
    const detallesUrl = "{{ route('obtener.detalles') }}";
    const notificacionesUrl = "{{ route('obtener.notificaciones') }}";
    const tablaNotificacionesUrl = "{{ route('obtener.tabla.notificaciones') }}";
    const detalleEvidenciaUrl = "{{ route('obtener.detalle.evidencia') }}";
    const verificarArchivosUrl = "{{ route('obligaciones.verificarArchivos') }}";
    const archivosListarUrl = "{{ route('archivos.listar') }}";
    const archivosEliminarUrl = "{{ route('archivos.eliminar') }}";
    const cambiarEstadoUrl = "{{ route('requisito.cambiarEstado') }}";
    const actualizarPorcentajeUrl = '{{ route('actualizar.porcentaje') }}';
    const actualizarSumaPorcentajeUrl = "{{ route('actualizar.suma.porcentaje') }}";
    const correoEnviarUrl = '{{ route('enviar.correo.datos.evidencia') }}';
    const approvedResulUrl = "{{ route('approved.resul') }}";
    const assetUrl = "{{ asset('') }}"; // Esto es útil para obtener la URL base para assets (imágenes, CSS, JS, etc.)
</script>

<script src="{{ asset('js/gestion_obligaciones/obligaciones/obligaciones.js') }}"></script>


<script>

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
    
    
    //Modal obligacion
    
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
        axios.post("{{ route('obtener.detalles') }}", {
            evidencia_id: evidenciaId
        })
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
                    <h5>${response.data.evidencia}</h5>
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
                        ${fechasLimiteHtml}<!-- Aquí se muestran todas las fechas -->
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
                    notificacionesHtml += `
                        <p><b>${nombre}</b></p>
                    `;
                });
            } else {
                notificacionesHtml += `
                    <p>No hay notificaciones</p>
                `;
            }
    
            notificacionesHtml += `
                    </div>
                </div>
            `;
    
            // Imprimir las notificaciones en el nuevo contenedor
            document.getElementById("notificaciones-info-" + requisitoId).innerHTML = notificacionesHtml;
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
            let tablaNotificacionesHtml = `
                <div class="info-container mt-2">
                    <div class="details-card">
                        <div class="section-header bg-light-grey">
                            <i class="fas fa-table"></i>
                            <span>Tabla de Notificaciones:</span>
                        </div>
                        <div class="table-responsive ">
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
                    </div>
                </div>
            `;
    
            // Imprimir la tabla de notificaciones en el nuevo contenedor
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
        // Realizar la solicitud inicial para obtener los detalles de la evidencia
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
                                    <img src="{{ asset('img/logo_svp.jpeg') }}" alt="Logo" class="logo" style="width: 70px; height: auto;">
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
                        
                        <button class="btn btn-secondary btnMarcarCumplido" id="btnMarcarCumplido" data-requisito-id="${response.data.id}" data-responsable="${response.data.responsable}">
                            <i class=""></i> Cambiar estado de evidencia
                        </button>   
                    `;
    
                    // Añadir evento click al botón después de insertarlo en el DOM
                    const btnMarcarCumplido = document.getElementById("btnMarcarCumplido");
                    btnMarcarCumplido.addEventListener('click', function() {
                        console.log(requisitoId);
                        console.log(response.data.fecha_limite_cumplimiento);
    
                        // Verificar la existencia de archivos usando Axios
                        axios.post("{{ route('obligaciones.verificarArchivos') }}", {
                            requisito_id: requisitoId,
                            fecha_limite_cumplimiento: response.data.fecha_limite_cumplimiento,
                            nombre_archivo: response.data.nombre_archivo
                        })
                        .then(function (verifyResponse) {
                            const conteo = verifyResponse.data.conteo;  // Obtener el conteo del backend
                            console.log(conteo);
                            if (conteo === 0) {  // Verificar si el conteo es 0
                                Swal.fire({
                                    title: "¡No hay archivos adjuntos para esta obligación!",
                                    text: "Para poder cambiar el estatus de la obligación se requiere mínimo un archivo adjunto.",
                                    icon: "error"
                                });
                            } else {
                                // Si existen registros, proceder con el cambio de estado
                                const requisitoId = btnMarcarCumplido.dataset.requisitoId;
                                const responsable = btnMarcarCumplido.dataset.responsable;
                                const numero_requisito = numeroRequisito;
    
                                actualizarEstado(requisitoId, responsable, numero_requisito);
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
                    correoEnviar();
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
                                <button class="btn btn-sm btn-danger btn-eliminar-archivo" onclick="eliminarArchivo(${archivo.id} , '${requisitoId}', '${evidenciaId}', '${fechaLimite}')">
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
    
    function actualizarPorcentaje(requisitoId) {
        axios.post('{{ route('actualizar.porcentaje') }}', {
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
    
    
    
    
    
    
    
    </script>
    
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Unificar la lógica para manejar la apertura del segundo modal y cargar los detalles
        document.querySelectorAll('.custom-card').forEach(function(element) {
            element.addEventListener('click', function() {
                const evidenciaId = this.dataset.evidenciaId;
                const idNotificaciones = this.dataset.idNotificaciones;
                const requisitoId = this.dataset.requisitoId;
    
                // Cerrar el primer modal antes de abrir el segundo
                const firstModal = document.getElementById('modal' + requisitoId);
                if (firstModal) {
                    $(firstModal).modal('hide');
                }
    
                // Cargar detalles de la evidencia, notificaciones y tabla de notificaciones
                obtenerDetallesEvidencia(evidenciaId, requisitoId);
                obtenerNotificaciones(idNotificaciones, requisitoId);
                obtenerTablaNotificaciones(idNotificaciones, requisitoId);
    
                // Hacer la solicitud al servidor para obtener el estado 'approved'
                axios.post("{{ route('approved.resul') }}", { id: requisitoId })
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
    
                // Abrir el segundo modal
                $('#modalDetalleContent').modal('show');
    
                // Guardar referencia al primer modal en el segundo modal para reiniciarlo después
                $('#modalDetalleContent').data('first-modal-id', 'modal' + requisitoId);
            });
        });
    
        // Cuando se cierra el segundo modal, reiniciar el primero
        $('#modalDetalleContent').on('hidden.bs.modal', function() {
            const firstModalId = $(this).data('first-modal-id');
            if (firstModalId) {
                // Reiniciar el contenido del primer modal
                const firstModal = document.getElementById(firstModalId);
                if (firstModal) {
                    $(firstModal).find('form').trigger('reset'); // Restablecer formularios
                    $(firstModal).find('.collapse').collapse('hide'); // Ocultar cualquier elemento colapsado
                    $(firstModal).modal('show');
                }
            }
        });
    
        // Limpiar el contenido del primer modal al cerrarlo
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form').trigger('reset'); // Restablecer formularios
            $(this).find('.collapse').collapse('hide'); // Ocultar cualquier elemento colapsado
            $(this).find('.info-container').empty(); // Limpiar contenido dinámico
        });
    });
    
    
    
    
    </script>
    
    
    
    <script>
      $(document).ready(function() {
        // Manejar la entrada de texto en el campo de búsqueda
        $('#buscarInput').on('input', function() {
            let searchText = $(this).val().toLowerCase();
            $('#cajaContainer .col-md-2').each(function() {
                let cardText = $(this).text().toLowerCase();
                $(this).toggle(cardText.includes(searchText));
            });
        });
    
        // Manejar el clic en las tarjetas de opción para abrir el modal
        $('.option-card[data-toggle="modal"]').on('click', function() {
            let targetModal = $(this).data('target');
            $(targetModal).modal('show');
        });
    
    
    });
    
    
    
    
    
    
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los elementos con la clase "status-indicator"
        const statusIndicators = document.querySelectorAll('.status-indicator');
    
        statusIndicators.forEach(function(indicator) {
            // Comprobar si el texto del indicador es "Completo"
            if (indicator.textContent.trim() === 'Completo') {
                // Cambiar el color de fondo a verde
                indicator.style.backgroundColor = 'green';
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los elementos con la clase "status-indicator"
        const statusIndicators = document.querySelectorAll('.status-indicator');
    
        statusIndicators.forEach(function(indicator) {
            // Comprobar si el texto del indicador es "Completo"
            if (indicator.textContent.trim() === 'Completo') {
                // Cambiar el color de fondo a verde
                indicator.style.backgroundColor = 'green';
            }
        });
    });
    
    document.addEventListener('DOMContentLoaded', function () {
        const avances = document.querySelectorAll('.avance-obligacion');
    
        avances.forEach(function (avance) {
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
    document.addEventListener('DOMContentLoaded', function () {
        const avances = document.querySelectorAll('.avance-obligacion');
    
        avances.forEach(function (avance) {
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
    
      