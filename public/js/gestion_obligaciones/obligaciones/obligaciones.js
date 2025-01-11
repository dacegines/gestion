// Función para validar que un ID sea un número entero válido
function isValidId(id) {
    return typeof id === "string" && id.trim().length > 0;
}

// Función para sanitizar entradas de texto
function sanitizeInput(input) {
    const element = document.createElement("div");
    element.textContent = input;
    return element.innerHTML;
}

// Inicialización y control de comportamiento en modales
document.addEventListener("DOMContentLoaded", function () {
    // Unificar la lógica para manejar la apertura del segundo modal y cargar los detalles
    document.querySelectorAll(".custom-card").forEach(function (element) {
        element.addEventListener("click", function () {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito;

            const firstModal = document.getElementById("modal" + requisitoId);
            if (firstModal) {
                $(firstModal).modal("hide");
            }

            obtenerDetallesEvidencia(evidenciaId, requisitoId);

            obtenerTablaNotificaciones(
                idNotificaciones,
                requisitoId,
                evidenciaId,
                numeroRequisito
            );

            // Realizar la solicitud para obtener el estado "approved"
            axios
                .post(approvedResultUrl, { id: requisitoId })
                .then(function (response) {
                    const aprobado = response.data.approved;
                    const elementoPrueba =
                        document.querySelector(".status-alert");
                    // Aquí puedes agregar lógica adicional para manejar el resultado
                })
                .catch(function (error) {
                    console.error(
                        "Error al obtener el estado approved:",
                        error
                    );
                });

            // Mostrar el modal y hacer los demás inertes
            $("#modalDetalleContent").modal("show");
            $("#modalDetalleContent").data(
                "first-modal-id",
                "modal" + requisitoId
            );

            // Desactivar otros modales y elementos fuera del modal activo
            $(".modal").not("#modalDetalleContent").attr("inert", "true");
        });
    });

    $("#modalDetalleContent").on("hidden.bs.modal", function () {
        const firstModalId = $(this).data("first-modal-id");
        if (firstModalId) {
            const firstModal = document.getElementById(firstModalId);
            if (firstModal) {
                $(firstModal).find("form").trigger("reset");
                $(firstModal).find(".collapse").collapse("hide");
                $(firstModal).modal("show");
            }
        }

        // Reactivar otros modales y elementos
        $(".modal").removeAttr("inert");
    });

    $(".modal").on("hidden.bs.modal", function () {
        $(this).find("form").trigger("reset");
        $(this).find(".collapse").collapse("hide");
        $(this).find(".info-container").empty();
    });
});

// Modal obligación - Carga de detalles y notificaciones
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".n_evidencia").forEach(function (element) {
        element.addEventListener("click", function () {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito;

            // Validar los IDs antes de hacer cualquier otra cosa
            if (
                !isValidId(evidenciaId) ||
                !isValidId(idNotificaciones) ||
                !isValidId(requisitoId)
            ) {
                Swal.fire("Error", "Datos no válidos detectados.", "error");
                return;
            }

            // Llamar a la función para obtener detalles de evidencia
            obtenerDetallesEvidencia(evidenciaId, requisitoId);

            // Llamar a la función para obtener notificaciones
            obtenerNotificaciones(idNotificaciones, requisitoId);

            // Obtener y mostrar la tabla de notificaciones
            obtenerTablaNotificaciones(
                idNotificaciones,
                requisitoId,
                evidenciaId,
                numeroRequisito
            );
        });
    });
});

function obtenerDetallesEvidencia(evidenciaId, requisitoId) {
    const year = document.getElementById("year-select").value; // Captura el año del formulario

    axios
        .post(obtenerDetallesEvidenciaUrl, {
            evidencia_id: evidenciaId,
            year: year,
        })
        .then(function (response) {
            let fechasLimiteHtml =
                '<ul style="list-style-type: disc; padding-left: 20px;">';
            if (
                response.data.fechas_limite_cumplimiento &&
                response.data.fechas_limite_cumplimiento.length > 0
            ) {
                response.data.fechas_limite_cumplimiento.forEach(function (
                    fecha
                ) {
                    fechasLimiteHtml += `<li><b>${sanitizeInput(
                        fecha
                    )}</b></li>`;
                });
            } else {
                fechasLimiteHtml =
                    "<p>No hay fechas límite de cumplimiento</p>";
            }
            fechasLimiteHtml += "</ul>";

            document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5><b>${sanitizeInput(response.data.condicion)}</b></h5>
            </div>
            <div class="details-card mt-2">
                <div class="info-section">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar"></i>
                        <span>Periodicidad:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.periodicidad
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-user"></i>
                        <span>Responsable:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.responsable
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Fechas límite de cumplimiento:</span>
                    </div>
                    ${fechasLimiteHtml}

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-file-alt"></i>
                        <span>Origen de la obligación:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.origen_obligacion
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-book"></i>
                        <span>Cláusula, condicionante, o artículo:</span>
                    </div>
                    ${
                        userRole === "invitado"
                            ? `
                            <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                        `
                            : `
                            <p style="text-align: justify;"><b>${sanitizeInput(
                                response.data.clausula_condicionante_articulo
                            )}</b></p>
                        `
                    }
                </div>
            </div>
        `;
        })
        .catch(function (error) {
            console.error("Error al obtener los detalles:", error);
        });
}

function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios
        .post(obtenerNotificacionesUrl, { id_notificaciones: idNotificaciones })
        .then(function (response) {
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
                response.data.forEach(function (nombre) {
                    notificacionesHtml += `<li><b>${sanitizeInput(
                        nombre
                    )}</b></li>`;
                });
            } else {
                notificacionesHtml += "<li>No hay notificaciones</li>";
            }

            notificacionesHtml += "</ul></div></div>";
            document.getElementById(
                "notificaciones-info-" + requisitoId
            ).innerHTML = notificacionesHtml;
        })
        .catch(function (error) {
            console.error("Error al obtener las notificaciones:", error);
        });
}

function obtenerTablaNotificaciones(
    idNotificaciones,
    requisitoId,
    evidenciaId,
    numeroRequisito
) {
    axios
        .post(obtenerTablaNotificacionesUrl, {
            id_notificaciones: idNotificaciones,
        })
        .then(function (response) {
            let tablaNotificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-table"></i>
                        <span>Tabla de Notificaciones:</span>
                    </div>
            `;

            // Lista de roles permitidos para mostrar el botón
            const allowedRoles = ["superUsuario"];

            // Verificar si el rol del usuario está dentro de los roles permitidos
            if (allowedRoles.includes(userRole) && response.data.length > 0) {
                tablaNotificacionesHtml += `
                <div class="d-flex justify-content-start mt-2">
                    <button class="btn btn-dark d-flex align-items-center gap-2" id="btn-agregar-${requisitoId}" onclick="mostrarFormulario(${requisitoId})">
                        <i class="fas fa-plus-circle"></i>
                        <span>Agregar a Notificaciones</span>
                    </button>
                </div>
                `;
            }
            let isSuperUsuario = userRole === "superUsuario";
            tablaNotificacionesHtml += `
                    <div id="formulario-agregar-${requisitoId}" class="mt-4 p-4 bg-light border rounded d-none">
                        <h5 class="mb-3 text-dark text-center">Agregar a Notificaciones</h5>
                        <form>
                            <!-- Campos Hidden -->
                            <input type="hidden" id="input-requisito-id-${requisitoId}" value="${numeroRequisito}">
                            <input type="hidden" id="input-notificacion-id1-${requisitoId}" value="${evidenciaId}">
                            <input type="hidden" id="input-notificacion-id2-${requisitoId}" value="${idNotificaciones}">

                            <div class="form-row">
                                <!-- Campo Puesto -->
                                <div class="form-group col-md-6">
                                    <label for="select-tipo-${requisitoId}" class="font-weight-bold">Puesto</label>
                                    <select id="select-tipo-${requisitoId}" class="form-control">
                                        <option value="">Seleccione un usuario</option>
                                    </select>
                                </div>

                                <!-- Campo Correo -->
                                <div class="form-group col-md-6">
                                    <label for="select-correo-${requisitoId}" class="font-weight-bold">Correo</label>
                                    <select id="select-correo-${requisitoId}" class="form-control" disabled>
                                        <option value="">Seleccione un correo</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Campo Notificación -->
                            <div class="form-group">
                                <label for="select-dias-${requisitoId}" class="font-weight-bold">Tipo de Notificación</label>
                                <select id="select-dias-${requisitoId}" class="form-control">
                                    <option value="primera_notificacion">1era Notificación</option>
                                    <option value="segunda_notificacion">2da Notificación</option>
                                    <option value="tercera_notificacion">3era Notificación</option>
                                </select>
                            </div>

                            <!-- Botones Centralizados -->
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button type="button" class="btn btn-success mx-2" onclick="guardarNotificacion(${requisitoId})">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-secondary mx-2" onclick="ocultarFormulario(${requisitoId})">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive mt-1">
                        <table class="styled-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Puesto</th>
                                    <th>Notificación</th>
                                    <th>Días</th>
                                    ${isSuperUsuario ? `<th>Eliminar</th>` : ""}
                                </tr>
                            </thead>
                            <tbody>
            `;

            if (response.data.length > 0) {
                response.data.forEach(function (notificacion) {
                    tablaNotificacionesHtml += `
                    <tr>
                        <td style="text-align: center;"><b>${sanitizeInput(
                            notificacion.nombre
                        )}</b></td>
                        <td style="text-align: center;"><b>${sanitizeInput(
                            notificacion.tipo
                        )}</b></td>
                        <td ${
                            notificacion.estilo
                        } style="text-align: center;"><b>${sanitizeInput(
                        notificacion.dias
                    )}</b></td>
                        ${
                            isSuperUsuario
                                ? `
                        <td style="text-align: center;">
                            <button class="btn btn-danger btn-sm" 
                                onclick="eliminarNotificacion(${notificacion.id}, ${requisitoId}, '${idNotificaciones}', '${evidenciaId}', '${numeroRequisito}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                        `
                                : ""
                        }
                    </tr>
                    `;
                });
            } else {
                tablaNotificacionesHtml += ` 
                    <tr>
                        <td colspan="${
                            isSuperUsuario ? 4 : 3
                        }" style="text-align: center;">No hay notificaciones</td>
                    </tr>`;
            }

            tablaNotificacionesHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            `;

            // Insertar contenido en el contenedor de la tabla
            document.getElementById(
                "tabla-notificaciones-info-" + requisitoId
            ).innerHTML = tablaNotificacionesHtml;
        })
        .catch(function (error) {
            console.error(
                "Error al obtener la tabla de notificaciones:",
                error
            );
        });
}

// Función para ocultar el formulario
function ocultarFormulario(requisitoId) {
    document
        .getElementById(`formulario-agregar-${requisitoId}`)
        .classList.add("d-none");
}

// Función para guardar la nueva notificación

function guardarNotificacion(requisitoId) {
    const puesto = document.getElementById(`select-tipo-${requisitoId}`).value;
    const correo = document.getElementById(`select-correo-${requisitoId}`).value;
    const notificacion = document.getElementById(`select-dias-${requisitoId}`).value;
    const numeroRequisito = document.getElementById(`input-requisito-id-${requisitoId}`).value;
    const evidenciaId = document.getElementById(`input-notificacion-id1-${requisitoId}`).value;
    const idNotificaciones = document.getElementById(`input-notificacion-id2-${requisitoId}`).value;

    if (!puesto) {
        Swal.fire({
            icon: "warning",
            title: "Puesto no seleccionado",
            text: "No se ha elegido un puesto válido. Por favor, seleccione un puesto.",
            confirmButtonText: "Aceptar",
        });
        return;
    }

    axios
        .post(guardarNotificacionUrl, {
            requisitoId,
            numeroRequisito,
            evidenciaId,
            idNotificaciones,
            nombre: puesto,
            email: correo,
            tipoNotificacion: notificacion,
        })
        .then((response) => {
            if (!response.data.success) {
                // Muestra la alerta de registro duplicado
                Swal.fire({
                    icon: "info",
                    title: "Por favor, seleccione otra opción.",
                    text: response.data.message,
                    confirmButtonText: "Aceptar",
                });
            } else {
                // Muestra la alerta de éxito y recarga la tabla de notificaciones
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: response.data.message,
                    confirmButtonText: "Aceptar",
                }).then(() => {
                    ocultarFormulario(requisitoId); // Oculta el formulario si se guarda con éxito
                    // Recarga la tabla de notificaciones
                    obtenerTablaNotificaciones(idNotificaciones, requisitoId, evidenciaId, numeroRequisito);
                });
            }
        })
        .catch((error) => {
            console.error("Error al guardar la notificación:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado al guardar.",
                confirmButtonText: "Aceptar",
            });
        });
}




function eliminarNotificacion(notificacionId, requisitoId, idNotificaciones, evidenciaId, numeroRequisito) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¡No podrás revertir esto!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(eliminarNotificacionUrl, {
                id: notificacionId,
                requisitoId: requisitoId,
                idNotificaciones: idNotificaciones,
                evidenciaId: evidenciaId,
                numeroRequisito: numeroRequisito,
            })
            .then((response) => {
                Swal.fire("¡Eliminado!", response.data.message, "success");
                obtenerTablaNotificaciones(idNotificaciones, requisitoId, evidenciaId, numeroRequisito);
            })
            .catch((error) => {
                console.error(error);
                Swal.fire("Error", "No se pudo eliminar la notificación.", "error");
            });
        }
    });
}




// Función para mostrar el formulario y cargar los usuarios
function mostrarFormulario(requisitoId) {
    document
        .getElementById(`formulario-agregar-${requisitoId}`)
        .classList.remove("d-none");

    // Obtener los selects del formulario
    const selectPuesto = document.getElementById(`select-tipo-${requisitoId}`);
    const selectCorreo = document.getElementById(
        `select-correo-${requisitoId}`
    );

    // Limpiar los selects antes de llenarlos
    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
    selectCorreo.innerHTML = '<option value="">Seleccione un correo</option>';

    // Llamada al endpoint para obtener los usuarios
    axios
        .get(usuariosUrl)
        .then((response) => {
            const usuarios = response.data;

            // Guardar la relación puesto-correo en un mapa para consulta rápida
            const puestoCorreoMap = {};

            usuarios.forEach((usuario) => {
                // Llenar el select de puestos
                const optionPuesto = document.createElement("option");
                optionPuesto.value = usuario.puesto;
                optionPuesto.textContent = `${usuario.name} - ${usuario.puesto}`;
                selectPuesto.appendChild(optionPuesto);

                // Guardar el correo asociado al puesto en el mapa
                puestoCorreoMap[usuario.puesto] = usuario.email;
            });

            // Evento para cambiar automáticamente el correo cuando se seleccione un puesto
            selectPuesto.addEventListener("change", function () {
                const puestoSeleccionado = this.value;
                const correoCorrespondiente =
                    puestoCorreoMap[puestoSeleccionado] || "";
                selectCorreo.innerHTML = `<option value="${correoCorrespondiente}">${
                    correoCorrespondiente || "Correo no disponible"
                }</option>`;
            });
        })
        .catch((error) => {
            console.error("Error al cargar la lista de usuarios:", error);
            alert("Ocurrió un error al cargar la lista de usuarios.");
        });
}

//modal detalle

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".custom-card").forEach(function (element) {
        element.addEventListener("click", function () {
            const detalleId = this.dataset.detalleId;
            const evidenciaId = this.dataset.evidenciaId;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito; // Recuperar el numero_requisito
            const fechaLimiteCumplimiento =
                this.dataset.fechaLimiteCumplimiento;

            cargarArchivos(requisitoId, evidenciaId, fechaLimiteCumplimiento);

            // Llamar a la función para cargar los detalles
            cargarDetalleEvidencia(
                detalleId,
                evidenciaId,
                requisitoId,
                numeroRequisito
            );
        });
    });
});

function cargarDetalleEvidencia(
    detalleId,
    evidenciaId,
    requisitoId,
    numeroRequisito
) {
    if (
        !isValidId(detalleId) ||
        !isValidId(evidenciaId) ||
        !isValidId(requisitoId)
    ) {
        console.error("IDs no válidos");
        return;
    }

    axios
        .post(obtenerDetalleEvidenciaUrl, {
            evidencia_id: sanitizeInput(evidenciaId),
            detalle_id: sanitizeInput(detalleId),
            requisito_id: sanitizeInput(requisitoId),
        })
        .then(function (response) {
            const modalElement = document.getElementById("modalDetalleContent");

            if (modalElement) {
                const infoSection = modalElement.querySelector(
                    ".modal-body .info-section"
                );

                if (infoSection) {
                    let content = `
                        <div class="header">
                            <h5><b>${sanitizeInput(
                                response.data.condicion
                            )}</b></h5>
                        </div>
                        <div class="details-card mt-2">
                            <div id="modal-detalles-obligacion" class="info-section">
                                <div class="logo-container" style="text-align: right;"></div>
                                <p style="display: none;"><b>${sanitizeInput(
                                    response.data.evidencia
                                )}</b></p> 
                                <p style="display: none;"><b>${sanitizeInput(
                                    response.data.nombre
                                )}</b></p>                         
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar"></i>
                                    <span>Periodicidad:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.periodicidad
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-user"></i>
                                    <span>Responsable:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.responsable
                                    )}</b></li>
                                </ul>    
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Fechas límite de cumplimiento:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.fecha_limite_cumplimiento
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Origen de la obligación:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.origen_obligacion
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-book"></i>
                                    <span>Cláusula, condicionante, o artículo:</span>
                                </div>
                                                            ${
                                                                userRole ===
                                                                "invitado"
                                                                    ? `
                                    <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                                `
                                                                    : `
                                    <p style="text-align: justify;"><b>${sanitizeInput(
                                        response.data
                                            .clausula_condicionante_articulo
                                    )}</b></p>
                                `
                                                            }
                            </div>
                        </div>
                        <br>
                    `;

                    // Lista de roles permitidos para mostrar el botón
                    const allowedRoles = ["admin", "superUsuario"];

                    // Verificar si el rol del usuario está dentro de los roles permitidos
                    if (allowedRoles.includes(userRole)) {
                        content += `
        <button class="btn btn-secondary btnMarcarCumplido w-100" id="btnMarcarCumplido" data-requisito-id="${sanitizeInput(
            response.data.id
        )}" data-responsable="${sanitizeInput(response.data.responsable)}">
            <i class=""></i> Cambiar estado de evidencia
        </button>
    `;
                    }

                    // Insertar el contenido en la sección de información
                    infoSection.innerHTML = content;

                    // Solo si el botón se ha renderizado, añadir la funcionalidad del evento click
                    const btnMarcarCumplido =
                        document.getElementById("btnMarcarCumplido");
                    if (btnMarcarCumplido) {
                        btnMarcarCumplido.addEventListener(
                            "click",
                            function () {
                                axios
                                    .post(verificarArchivosUrl, {
                                        requisito_id:
                                            sanitizeInput(requisitoId),
                                        fecha_limite_cumplimiento:
                                            sanitizeInput(
                                                response.data
                                                    .fecha_limite_cumplimiento
                                            ),
                                        nombre_archivo: sanitizeInput(
                                            response.data.nombre_archivo
                                        ),
                                    })
                                    .then(function (verifyResponse) {
                                        if (verifyResponse.data.conteo === 0) {
                                            Swal.fire({
                                                title: "¡No hay archivos adjuntos para esta evidencia!",
                                                text: "Para poder cambiar el estatus de la evidencia se requiere mínimo un archivo adjunto.",
                                                icon: "error",
                                            });
                                        } else {
                                            actualizarEstado(
                                                detalleId,
                                                requisitoId,
                                                sanitizeInput(
                                                    response.data.responsable
                                                ),
                                                sanitizeInput(numeroRequisito)
                                            );
                                        }
                                    })
                                    .catch(function (error) {
                                        console.error(
                                            "Error al verificar los archivos:",
                                            error
                                        );
                                    });
                            }
                        );
                    }
                } else {
                    console.error(
                        "No se encontró la sección de información en el modal"
                    );
                }
            } else {
                console.error(
                    "No se encontró el modal con ID modalDetalle" +
                        sanitizeInput(detalleId)
                );
            }
        })
        .catch(function (error) {
            console.error("Error al obtener los detalles:", error);
        });
}

// Subir archivo
document.querySelectorAll(".custom-card").forEach(function (element) {
    element.addEventListener("click", function () {
        const requisitoId = this.dataset.requisitoId;
        const evidenciaId = this.dataset.evidenciaId;
        const fechaLimite = this.dataset.fechaLimiteCumplimiento;

        // Asignar los valores al formulario
        document.querySelector('#uploadForm input[name="requisito_id"]').value =
            requisitoId;
        document.querySelector('#uploadForm input[name="evidencia"]').value =
            evidenciaId;
        document.querySelector(
            '#uploadForm input[name="fecha_limite_cumplimiento"]'
        ).value = fechaLimite;
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
        Swal.fire(
            "Error",
            "Favor de verificar ya que no se tiene ningún archivo adjunto.",
            "warning"
        );
        return; // Detener la ejecución si no hay archivo adjunto
    }

    // Validar el tamaño del archivo (máximo 20MB en este ejemplo)
    const maxFileSize = 20 * 1024 * 1024; // 20MB
    if (archivoAdjunto.size > maxFileSize) {
        Swal.fire(
            "Error",
            "El archivo es demasiado grande. Comuníquese con el administrador del sistema.",
            "warning"
        );
        return; // Detener la ejecución si el archivo es demasiado grande
    }

    // Validar el tipo de archivo
    const validFileTypes = [
        "application/pdf", // PDF
        "image/jpeg", // JPEG
        "image/png", // PNG
        "image/gif", // GIF
        "application/msword", // DOC
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // DOCX
        "application/vnd.ms-excel", // XLS
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", // XLSX
        "application/vnd.ms-powerpoint", // PPT
        "application/vnd.openxmlformats-officedocument.presentationml.presentation", // PPTX
        "text/plain", // TXT
        "application/zip", // ZIP
        "application/x-rar-compressed", // RAR
    ];
    if (!validFileTypes.includes(archivoAdjunto.type)) {
        Swal.fire(
            "Error",
            "Tipo de archivo no permitido. Los formatos permitidos son PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPEG, PNG, GIF, ZIP y RAR.",
            "warning"
        );
        return; // Detener la ejecución si el tipo de archivo no es permitido
    }

    // Iterar sobre los datos del formulario y extraer los valores específicos
    for (var pair of formData.entries()) {
        if (pair[0] === "requisito_id") {
            requisitoId = pair[1];
        } else if (pair[0] === "evidencia") {
            evidenciaId = pair[1];
        } else if (pair[0] === "fecha_limite_cumplimiento") {
            fechaLimite = pair[1];
        }
    }

    Swal.fire({
        title: "¿Estás seguro?",
        html: "Al subir este archivo se enviará una notificación vía correo a: <br>Gerente Jurídico, <br>Jefa de Cumplimiento.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, subir archivo",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar la imagen de progreso al confirmar
            const loader = document.getElementById("loader");
            loader.style.display = "block";

            // Si el usuario confirma, se procede con la subida del archivo
            axios
                .post(form.action, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then(function (response) {
                    Swal.fire(
                        "Éxito",
                        "El archivo se subió correctamente.",
                        "success"
                    );
                    cargarArchivos(requisitoId, evidenciaId, fechaLimite); // Recargar archivos
                    form.reset(); // Limpiar el formulario tras una subida exitosa
                })
                .catch(function (error) {
                    let errorMessage = "Error al subir el archivo.";
                    if (error.response && error.response.status === 413) {
                        errorMessage = "El archivo es demasiado grande.";
                    }
                    Swal.fire("Error", errorMessage, "error");
                })
                .finally(function () {
                    loader.style.display = "none"; // Ocultar la imagen de progreso al finalizar
                });
        }
    });
}

function correoEnviar() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios
        .post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
        .then(function (response) {
            Swal.fire("Éxito", "El correo se envió correctamente.", "success");
        })
        .catch(function (error) {
            console.error("Error al enviar el correo:", error);
            Swal.fire(
                "Error",
                "Hubo un problema al enviar el correo.",
                "error"
            );
        });
}

// Recargar los archivos para un requisito específico
function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    axios
        .post(listarArchivosUrl, {
            requisito_id: sanitizeInput(requisitoId),
            evidencia_id: sanitizeInput(evidenciaId),
            fecha_limite: sanitizeInput(fechaLimite),
        })
        .then(function (response) {
            const archivos = response.data.archivos;
            let tableBody =
                archivos.length > 0
                    ? archivos
                          .map(
                              (archivo, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${sanitizeInput(
                    archivo.nombre_archivo.split("_").slice(1).join("_")
                )}</td>
                <td>${sanitizeInput(archivo.usuario)}</td>
                <td>${sanitizeInput(archivo.puesto)}</td>
                <td>${new Date(
                    sanitizeInput(archivo.created_at)
                ).toLocaleString()}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-info btn-ver-archivo" 
                        data-url="${storageUploadsUrl}/${sanitizeInput(
                                  archivo.nombre_archivo
                              )}"
                        ${userRole === "invitado" ? "disabled" : ""}
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
                <td>
                    <button 
                        class="btn btn-sm btn-danger btn-eliminar-archivo" 
                        data-id="${sanitizeInput(archivo.id)}" 
                        data-url="${storageUploadsUrl}/${sanitizeInput(
                                  archivo.nombre_archivo
                              )}"
                        data-requisito-id="${sanitizeInput(requisitoId)}" 
                        data-evidencia-id="${sanitizeInput(evidenciaId)}" 
                        data-fecha-limite="${sanitizeInput(fechaLimite)}"
                        ${
                            ["admin", "superUsuario"].includes(userRole)
                                ? ""
                                : "disabled"
                        }
                    >
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
                <td>
                    <button 
                        class="btn btn-sm btn-success btn-descargar-archivo" 
                        data-url="${storageUploadsUrl}/${sanitizeInput(
                                  archivo.nombre_archivo
                              )}"
                        ${userRole === "invitado" ? "disabled" : ""}
                    >
                        <i class="fas fa-download"></i>
                    </button>
                </td>
            </tr>`
                          )
                          .join("")
                    : '<tr><td colspan="8">No hay archivos adjuntos</td></tr>';

            document.getElementById("archivosTableBody").innerHTML = tableBody;

            agregarEventos(); // Agregar eventos a los botones
        })
        .catch(function (error) {
            console.error("Error al cargar los archivos:", error);
        });
}
function agregarEventos() {
    // Evento para el botón "Ver"
    document.querySelectorAll(".btn-ver-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const fileUrl = this.dataset.url;
            window.open(fileUrl, "_blank"); // Abrir en una nueva pestaña
        });
    });

    // Evento para el botón "Eliminar"
    document.querySelectorAll(".btn-eliminar-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const archivoId = this.dataset.id;
            const archivoUrl = this.dataset.url;
            const requisitoId = this.dataset.requisitoId; // Asegúrate que el botón tenga este atributo
            const evidenciaId = this.dataset.evidenciaId; // Asegúrate que el botón tenga este atributo
            const fechaLimite = this.dataset.fechaLimite; // Asegúrate que el botón tenga este atributo

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Este archivo se eliminará permanentemente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .post(eliminarArchivoUrl, {
                            id: archivoId,
                            ruta_archivo: archivoUrl,
                        })
                        .then((response) => {
                            if (response.data.success) {
                                Swal.fire(
                                    "Eliminado",
                                    response.data.message,
                                    "success"
                                );
                                cargarArchivos(
                                    requisitoId,
                                    evidenciaId,
                                    fechaLimite
                                ); // Ahora sí pasamos los valores correctos
                            } else {
                                Swal.fire(
                                    "Error",
                                    response.data.message,
                                    "error"
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error al eliminar el archivo:",
                                error
                            );
                            Swal.fire(
                                "Error",
                                "Ocurrió un problema al eliminar el archivo.",
                                "error"
                            );
                        });
                }
            });
        });
    });

    // Evento para el botón "Descargar"
    document.querySelectorAll(".btn-descargar-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const fileUrl = this.dataset.url; // URL del archivo
            const fileName = fileUrl.split("/").pop(); // Extraer el nombre del archivo

            // Crear un enlace temporal para forzar la descarga
            const link = document.createElement("a");
            link.href = fileUrl;
            link.download = fileName;
            document.body.appendChild(link); // Agregar al DOM
            link.click(); // Simular clic para descargar
            document.body.removeChild(link); // Eliminar del DOM
        });
    });
}

// Marcado como revisado
function actualizarEstado(
    detalleId,
    requisitoId,
    responsable,
    numero_requisito
) {
    console.log("el valor es: " + detalleId);

    // Mostrar alerta de confirmación con SweetAlert2
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Está a punto de modificar el estatus de esta obligación. Se notificará al responsable correspondiente por correo electrónico.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, cambiar estatus",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "El estado de la obligación ha sido cambiado.<br>Se notificará al responsable por correo electrónico.",
                showConfirmButton: false,
                timer: 6000,
            });

            actualizarPorcentaje(detalleId);
            actualizarPorcentajeSuma(detalleId, numero_requisito);
            // Si el usuario confirma, realiza la solicitud para cambiar el estado
            axios
                .post(cambiarEstadoRequisitoUrl, {
                    id: detalleId,
                })
                .then(function (response) {
                    if (response.data.success) {
                        console.log(
                            "Nuevo estado del requisito:",
                            response.data.approved
                        );

                        const button = document.querySelector(
                            `.btnMarcarCumplido[data-requisito-id="${requisitoId}"]`
                        );

                        // Aquí ejecutamos la lógica para actualizar el contenido del elemento con la clase 'status-alert'
                        const aprobado = response.data.approved;
                        const elementoPrueba =
                            document.querySelector(".status-alert");

                        if (aprobado) {
                            // Establecer la clase de éxito y el mensaje correspondiente
                            elementoPrueba.classList.remove("alert-danger");
                            elementoPrueba.classList.add("alert-success");
                            elementoPrueba.innerHTML =
                                '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
                        } else {
                            // Establecer la clase de error y el mensaje correspondiente
                            elementoPrueba.classList.remove("alert-success");
                            elementoPrueba.classList.add("alert-danger");
                            elementoPrueba.innerHTML =
                                '<strong><i class="fas fa-times"></i></strong> Esta obligación volvió a su estatus inicial.';
                        }
                    } else {
                        console.error(
                            "Error al actualizar el estado:",
                            response.data.error
                        );
                    }
                })
                .catch(function (error) {
                    console.error("Error en la solicitud:", error);
                });
        }
    });
}

function abrirModalDetalle(detalleId, requisitoId) {
    // Asegúrate de que los valores no están vacíos
    if (!detalleId || !requisitoId) {
        console.error("detalleId o requisitoId no están definidos");
        return;
    }

    // Ahora puedes usar estos valores para hacer tus solicitudes y abrir el modal
    $("#modalDetalleContent").modal("show"); // Abrir el modal

    // Hacer la solicitud al servidor para obtener el estado 'approved'
    axios
        .post(obtenerEstadoAprobadoUrl, {
            id: detalleId,
        })
        .then(function (response) {
            let aprobado = response.data.approved;
            const elementoPrueba = document.querySelector(".status-alert");

            if (aprobado) {
                // Establecer la clase de éxito y el mensaje correspondiente
                elementoPrueba.classList.remove("alert-danger");
                elementoPrueba.classList.add("alert-success");
                elementoPrueba.innerHTML =
                    '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
            } else {
                // Establecer la clase de error y el mensaje correspondiente
                elementoPrueba.classList.remove("alert-success");
                elementoPrueba.classList.add("alert-danger");
                elementoPrueba.innerHTML =
                    '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada o volvió a su estatus inicial.';
            }
        })
        .catch(function (error) {
            console.error("Error al obtener el estado approved:", error);
        });
}

function obtenerDatosInfoSection() {
    const infoSection = document.querySelector("#modal-detalles-obligacion");
    const datos = {};

    if (infoSection) {
        const evidenciaElement = infoSection.querySelector(
            'p[style*="display: none;"] b'
        );
        const nombreElement = infoSection.querySelector("p:nth-child(2) b"); // Recupera el nombre
        const periodicidadElement = infoSection.querySelector(
            ".section-header + p"
        );
        const responsableElement = infoSection.querySelector(
            ".section-header + p + .section-header + p"
        );
        const fechaLimiteElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p"
        );
        const origenObligacionElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p + .section-header + p"
        );
        const clausulaElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p + .section-header + p + .section-header + p"
        );

        // Asignar valores al objeto datos
        datos.evidencia = evidenciaElement
            ? evidenciaElement.textContent.trim()
            : "";
        datos.nombre = nombreElement ? nombreElement.textContent.trim() : "";
        datos.periodicidad = periodicidadElement
            ? periodicidadElement.textContent.trim()
            : "";
        datos.responsable = responsableElement
            ? responsableElement.textContent.trim()
            : "";
        datos.fecha_limite_cumplimiento = fechaLimiteElement
            ? fechaLimiteElement.textContent.trim()
            : "";
        datos.origen_obligacion = origenObligacionElement
            ? origenObligacionElement.textContent.trim()
            : "";
        datos.clausula_condicionante_articulo = clausulaElement
            ? clausulaElement.textContent.trim()
            : "";
    } else {
        console.error(
            'No se encontró la sección de información con la clase "info-section".'
        );
    }

    return datos;
}

// Ejemplo de función que llama a `obtenerDatosInfoSection`
function ejecutarAccionConDatos() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios
        .post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
        .then(function (response) {
            console.log("Correo enviado correctamente:", response.data);
            Swal.fire("Éxito", "El correo se envió correctamente.", "success");
        })
        .catch(function (error) {
            console.error("Error al enviar el correo:", error);
            Swal.fire(
                "Error",
                "Hubo un problema al enviar el correo.",
                "error"
            );
        });
}

function cambiarEstadoEvidencia(requisitoId, evidenciaId) {
    // Cambiar el estado de la evidencia
    axios
        .post(cambiarEstadoRequisitoUrl, {
            id: requisitoId,
        })
        .then(function (response) {
            if (response.data.success) {
                Swal.fire(
                    "Éxito",
                    "El estado de la evidencia ha sido cambiado.",
                    "success"
                );
            } else {
                throw new Error("Error al cambiar el estado");
            }
        })
        .catch(function (error) {
            console.error("Error:", error);
            Swal.fire("Error", "Hubo un problema durante el proceso.", "error");
        });
}

function actualizarPorcentaje(detalleId) {
    if (!isValidId(detalleId)) {
        console.error("ID no válido");
        return;
    }

    axios
        .post(actualizarPorcentajeUrl, {
            id: sanitizeInput(detalleId),
        })
        .then(function (response) {
            if (response.data.success) {
                console.log(
                    "Porcentaje actualizado correctamente:",
                    response.data
                );
                // Puedes agregar más lógica aquí si es necesario
            } else {
                throw new Error("Error al actualizar el porcentaje");
            }
        })
        .catch(function (error) {
            console.error("Error al actualizar el porcentaje:", error);
        });
}

function actualizarPorcentajeSuma(detalleId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(numeroRequisito)) {
        console.error("IDs no válidos");
        return;
    }

    axios
        .post(actualizarSumaPorcentajeUrl, {
            requisito_id: sanitizeInput(detalleId),
            numero_requisito: sanitizeInput(numeroRequisito),
        })
        .then(function (response) {
            console.log(
                "Número de registros encontrados:",
                response.data.conteo
            );
            console.log(
                "Porcentaje por cada registro:",
                response.data.porcentaje_por_registro
            );
        })
        .catch(function (error) {
            console.error("Error al contar los registros:", error);
        });
}

// Cambiar color de estado de avance
document.addEventListener("DOMContentLoaded", function () {
    const statusIndicators = document.querySelectorAll(".status-indicator");

    statusIndicators.forEach(function (indicator) {
        if (indicator.textContent.trim() === "Completo") {
            indicator.style.backgroundColor = "green";
        }
    });

    const avances = document.querySelectorAll(".avance-obligacion");

    avances.forEach(function (avance) {
        const valorAvance = parseInt(avance.getAttribute("data-avance"), 10);
        let colorClase = "";

        if (valorAvance >= 0 && valorAvance <= 15) {
            colorClase = "avance-rojo";
        } else if (valorAvance >= 16 && valorAvance <= 50) {
            colorClase = "avance-naranja";
        } else if (valorAvance >= 51 && valorAvance <= 99) {
            colorClase = "avance-amarillo";
        } else if (valorAvance == 100) {
            colorClase = "avance-verde";
        }

        avance.classList.add(colorClase);
    });
});

$("#modalDetalleContent").on("show.bs.modal", function () {
    // Añadir 'inert' a todos los demás modales y elementos que no deberían ser interactivos
    $(".modal").not(this).attr("inert", "true");

    // Opcionalmente, puedes agregar 'inert' a otros elementos que no deberían ser accesibles
    // Ejemplo:
    // $('header, footer, .sidebar').attr('inert', 'true');
});

$("#modalDetalleContent").on("hidden.bs.modal", function () {
    // Quitar 'inert' cuando el modal se cierra para restaurar la interactividad
    $(".modal").removeAttr("inert");

    // Si agregaste 'inert' a otros elementos, quítalo también
    // Ejemplo:
    // $('header, footer, .sidebar').removeAttr('inert');
});

function enviarAlertaCorreo(diasRestantes) {
    console.log("Botón de alerta clicado para " + diasRestantes + " días");

    axios
        .post(enviarCorreoAlertaUrl, {
            dias_restantes: diasRestantes,
        })
        .then((response) => {
            console.log("Correo enviado:", response.data);
            Swal.fire({
                title: "Correo Enviado",
                text: `Se ha enviado un correo para la alerta de ${diasRestantes} días.`,
                icon: "success",
            });
        })
        .catch((error) => {
            console.error("Error al enviar el correo:", error);
            Swal.fire({
                title: "Error",
                text: "Hubo un problema al enviar el correo.",
                icon: "error",
            });
        });
}
