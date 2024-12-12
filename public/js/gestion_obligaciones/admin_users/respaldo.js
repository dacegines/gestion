// Boton Editar Usuario
$(document).on('click', '.edit-user-btn', function () {
    const userId = $(this).data('id');
    const userName = $(this).data('name');
    const userEmail = $(this).data('email');
    const userPuesto = $(this).data('puesto');

    // Llenar los campos del modal
    $('#editUserId').val(userId);
    $('#editUserName').val(userName);
    $('#editUserEmail').val(userEmail);
    $('#editUserPuesto').val(userPuesto);
});


//Borrar Usuario
document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.delete-user-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Evita el envío inmediato

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará al usuario y todos sus permisos y roles asociados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Envía el formulario si se confirma
                }
            });
        });
    });
});

// Actualizar Rol
$(document).on('click', '.role-btn', function () {
    // Obtener los valores del botón clicado
    const userId = $(this).data('id');
    const userName = $(this).data('name');
    const userEmail = $(this).data('email');

    // Rellenar el campo oculto con el ID del usuario
    $('#modelIdRoleInput').val(userId);

    // Rellenar el campo visible con el nombre y correo
    $('#userNameEmailRoleInput').val(`${userName} - ${userEmail}`);
});

// Actualizar Area
$(document).on('click', '.area-btn', function () {
    // Obtener los valores del botón clicado
    const userId = $(this).data('id');
    const userName = $(this).data('name');
    const userEmail = $(this).data('email');

    // Rellenar el campo oculto con el ID del usuario
    $('#modelIdInput').val(userId);

    // Rellenar el campo visible con el nombre y correo
    $('#userNameEmailInput').val(`${userName} - ${userEmail}`);
});

// Crear usuario nuevo
$(document).ready(function () {
    $('#create-user-form').on('submit', function (e) {
        e.preventDefault(); // Evita recargar la página

        const form = $(this);
        const submitBtn = $('#submit-btn');

        // Deshabilitar el botón mientras se envía la solicitud
        submitBtn.prop('disabled', true);

        // Enviar los datos del formulario con AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(), // Serializar los datos del formulario
            success: function (response) {
                // Mostrar alerta de éxito con SweetAlert2 solo al enviar correctamente
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario creado',
                    text: 'El usuario se ha creado correctamente.',
                    confirmButtonText: 'Aceptar'
                });

                // Resetear el formulario
                form[0].reset();

                // Ocultar el botón de envío nuevamente
                submitBtn.hide();

                // Cerrar el modal después de crear el usuario
                $('#createUserModal').modal('hide');
            },
            error: function (xhr) {
                // Manejo de errores
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage = 'Ocurrió un error. Por favor, inténtalo nuevamente.';

                if (Object.keys(errors).length > 0) {
                    // Construir mensaje de error a partir de los errores de validación
                    errorMessage = Object.values(errors).join('\n');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error al crear usuario',
                    text: errorMessage,
                    confirmButtonText: 'Aceptar'
                });
            },
            complete: function () {
                // Rehabilitar el botón
                submitBtn.prop('disabled', false);
            }
        });
    });
});

// validacion de correo en formulario
$(document).ready(function () {
    $('#email').on('blur', function () {
        const email = $(this).val();
        const feedback = $('#email-feedback');

        if (email) {
            $.ajax({
                url: "{{ route('check.email') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    email: email
                },
                success: function (response) {
                    if (response.status === 'error') {
                        feedback.text(response.message).addClass('text-danger').removeClass('text-success');
                    } else {
                        feedback.text(response.message).addClass('text-success').removeClass('text-danger');
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    feedback.text('Ocurrió un error al validar el correo.').addClass('text-danger');
                }
            });
        } else {
            feedback.text('');
        }
    });
});

//validacion de formulario de nuevo usuario
$(document).ready(function () {
    const emailField = $('#email');
    const nameField = $('input[name="name"]');
    const puestoField = $('input[name="puesto"]');
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $('#email-feedback');
    const submitBtn = $('#submit-btn');

    // Función para verificar si todos los campos están llenos
    function allFieldsFilled() {
        return (
            nameField.val().trim() !== '' &&
            puestoField.val().trim() !== '' &&
            emailField.val().trim() !== '' &&
            passwordField.val().trim() !== '' &&
            passwordConfirmField.val().trim() !== ''
        );
    }

    // Validar correo dinámicamente
    emailField.on('blur', function () {
        const email = $(this).val();

        if (email) {
            $.ajax({
                url: "{{ route('check.email') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    email: email
                },
                success: function (response) {
                    if (response.status === 'error') {
                        feedback.text(response.message).addClass('text-danger').removeClass('text-success');
                        submitBtn.hide(); // Ocultar el botón si el correo no es válido
                    } else {
                        feedback.text(response.message).addClass('text-success').removeClass('text-danger');
                        if (allFieldsFilled()) {
                            submitBtn.show(); // Mostrar el botón si todos los campos están llenos y el correo es válido
                        }
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    feedback.text('Ocurrió un error al validar el correo.').addClass('text-danger');
                    submitBtn.hide(); // Ocultar el botón en caso de error
                }
            });
        } else {
            feedback.text('');
            submitBtn.hide(); // Ocultar el botón si el correo está vacío
        }
    });

    // Verificar todos los campos al cambiar cualquier valor
    $('input').on('input', function () {
        if (allFieldsFilled() && feedback.hasClass('text-success')) {
            submitBtn.show(); // Mostrar el botón si todos los campos están llenos y el correo es válido
        } else {
            submitBtn.hide(); // Ocultar el botón si falta algún campo o el correo no es válido
        }
    });
});

$(document).ready(function () {
    const emailField = $('#email');
    const nameField = $('input[name="name"]');
    const puestoField = $('input[name="puesto"]');
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $('#email-feedback');
    const submitBtn = $('#submit-btn');

    // Elementos para mensajes de validación
    const requirements = $('#password-requirements');
    const lengthReq = $('#length');
    const uppercaseReq = $('#uppercase');
    const numberReq = $('#number');
    const specialReq = $('#special');

    // Expresiones regulares para validaciones de contraseña
    const uppercaseRegex = /[A-Z]/;
    const numberRegex = /[0-9]/;
    const specialCharRegex = /[!@#$%^&*]/;

    // Validar si todos los campos están llenos y correctos
    function validateForm() {
        const emailValid = feedback.hasClass('text-success');
        const password = passwordField.val();
        const confirmPassword = passwordConfirmField.val();

        const passwordValid =
            password.length >= 8 &&
            uppercaseRegex.test(password) &&
            numberRegex.test(password) &&
            specialCharRegex.test(password);

        const passwordsMatch = password === confirmPassword;

        return (
            nameField.val().trim() !== '' &&
            puestoField.val().trim() !== '' &&
            emailField.val().trim() !== '' &&
            passwordValid &&
            passwordsMatch &&
            emailValid
        );
    }

    // Habilitar o deshabilitar el botón
    function toggleSubmitButton() {
        if (validateForm()) {
            submitBtn.show(); // Mostrar el botón si todo está válido
        } else {
            submitBtn.hide(); // Ocultar el botón si algo no está válido
        }
    }

    // Validar contraseña en tiempo real
    passwordField.on('input', function () {
        const password = $(this).val();

        // Validar longitud
        lengthReq.toggleClass('text-success', password.length >= 8)
                 .toggleClass('text-danger', password.length < 8);

        // Validar mayúsculas
        uppercaseReq.toggleClass('text-success', uppercaseRegex.test(password))
                    .toggleClass('text-danger', !uppercaseRegex.test(password));

        // Validar números
        numberReq.toggleClass('text-success', numberRegex.test(password))
                 .toggleClass('text-danger', !numberRegex.test(password));

        // Validar caracteres especiales
        specialReq.toggleClass('text-success', specialCharRegex.test(password))
                  .toggleClass('text-danger', !specialCharRegex.test(password));

        toggleSubmitButton();
    });

    // Validar confirmación de contraseña
    passwordConfirmField.on('input', function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();
        const feedback = $('#password-feedback');

        if (password !== confirmPassword) {
            feedback.text('Las contraseñas no coinciden.').addClass('text-danger').removeClass('text-success');
        } else {
            feedback.text('Las contraseñas coinciden.').addClass('text-success').removeClass('text-danger');
        }

        toggleSubmitButton();
    });

    // Validar correo dinámicamente
    emailField.on('blur', function () {
        const email = $(this).val();

        if (email) {
            $.ajax({
                url: "{{ route('check.email') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    email: email
                },
                success: function (response) {
                    if (response.status === 'error') {
                        feedback.text(response.message).addClass('text-danger').removeClass('text-success');
                        toggleSubmitButton();
                    } else {
                        feedback.text(response.message).addClass('text-success').removeClass('text-danger');
                        toggleSubmitButton();
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    feedback.text('Ocurrió un error al validar el correo.').addClass('text-danger');
                    toggleSubmitButton();
                }
            });
        } else {
            feedback.text('');
            toggleSubmitButton();
        }
    });

    // Validar todos los campos al escribir en cualquier input
    $('input').on('input', toggleSubmitButton);
});


    $(document).ready(function () {
    const passwordField = $('#password');
    const submitBtn = $('#submit-btn');

    // Elementos para mensajes de validación
    const requirements = $('#password-requirements');
    const lengthReq = $('#length');
    const uppercaseReq = $('#uppercase');
    const numberReq = $('#number');
    const specialReq = $('#special');

    // Expresiones regulares para validaciones
    const uppercaseRegex = /[A-Z]/;
    const numberRegex = /[0-9]/;
    const specialCharRegex = /[!@#$%^&*]/;

    // Mostrar los requisitos al interactuar con el campo
    passwordField.on('focus', function () {
        requirements.removeClass('d-none');
    });

    // Validar requisitos de la contraseña en tiempo real
    passwordField.on('input', function () {
        const password = $(this).val();

        // Validar longitud
        lengthReq.toggleClass('text-success', password.length >= 8)
                 .toggleClass('text-danger', password.length < 8);

        // Validar mayúsculas
        uppercaseReq.toggleClass('text-success', uppercaseRegex.test(password))
                    .toggleClass('text-danger', !uppercaseRegex.test(password));

        // Validar números
        numberReq.toggleClass('text-success', numberRegex.test(password))
                 .toggleClass('text-danger', !numberRegex.test(password));

        // Validar caracteres especiales
        specialReq.toggleClass('text-success', specialCharRegex.test(password))
                  .toggleClass('text-danger', !specialCharRegex.test(password));

        // Mostrar botón si todos los requisitos se cumplen
        const allValid = password.length >= 8 &&
                         uppercaseRegex.test(password) &&
                         numberRegex.test(password) &&
                         specialCharRegex.test(password);
        submitBtn.toggle(allValid);
    });

    // Ocultar los requisitos cuando el campo pierde el foco si todos están cumplidos
    passwordField.on('blur', function () {
        const password = $(this).val();

        if (password.length >= 8 &&
            uppercaseRegex.test(password) &&
            numberRegex.test(password) &&
            specialCharRegex.test(password)) {
            requirements.addClass('d-none');
        }
    });
});

$(document).ready(function () {
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $('#password-feedback');
    const submitBtn = $('#submit-btn');

    // Validar contraseñas dinámicamente
    passwordConfirmField.on('input', function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();

        if (password !== confirmPassword) {
            feedback.text('Las contraseñas no coinciden. Por favor verifica e inténtalo nuevamente.')
                    .addClass('text-danger')
                    .removeClass('text-success');
            submitBtn.hide(); // Ocultar el botón si las contraseñas no coinciden
        } else {
            feedback.text('Las contraseñas coinciden.').addClass('text-success').removeClass('text-danger');
            if (validateForm()) {
                submitBtn.show(); // Mostrar el botón si todo está correcto
            }
        }
    });

    // Validar si todos los campos están llenos y correctos
    function validateForm() {
        const password = passwordField.val();
        const confirmPassword = passwordConfirmField.val();

        return (
            $('input[name="name"]').val().trim() !== '' &&
            $('input[name="puesto"]').val().trim() !== '' &&
            $('input[name="email"]').val().trim() !== '' &&
            password.trim() !== '' &&
            confirmPassword.trim() !== '' &&
            password === confirmPassword
        );
    }
});

$(document).ready(function () {
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $('#password-feedback');
    const submitBtn = $('#submit-btn');

    // Validar contraseñas dinámicamente
    passwordConfirmField.on('blur', function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();

        if (password !== confirmPassword) {
            feedback.text('Las contraseñas no coinciden.').addClass('text-danger').removeClass('text-success');
            submitBtn.hide(); // Ocultar el botón si las contraseñas no coinciden
        } else if (password === '') {
            feedback.text('');
            submitBtn.hide(); // Ocultar el botón si no se han llenado las contraseñas
        } else {
            feedback.text('Las contraseñas coinciden.').addClass('text-success').removeClass('text-danger');
            if (allFieldsFilled()) {
                submitBtn.show(); // Mostrar el botón si todo está correcto
            }
        }
    });

    // Verificar campos llenos (reutiliza la función si ya la tienes)
    function allFieldsFilled() {
        return (
            $('input[name="name"]').val().trim() !== '' &&
            $('input[name="puesto"]').val().trim() !== '' &&
            $('input[name="email"]').val().trim() !== '' &&
            passwordField.val().trim() !== '' &&
            passwordConfirmField.val().trim() !== ''
        );
    }

    // Mostrar u ocultar el botón en tiempo real
    $('input').on('input', function () {
        if (allFieldsFilled() && feedback.hasClass('text-success')) {
            submitBtn.show();
        } else {
            submitBtn.hide();
        }
    });
});

// Ver contrasenas escritas
$(document).on('click', '.toggle-password', function () {
    const target = $(this).data('target'); // Obtener el ID del campo de contraseña
    const input = $(target);
    const icon = $(this).find('i');

    // Alternar entre 'password' y 'text'
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash'); // Cambiar el ícono
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye'); // Cambiar el ícono
    }
});


//datatable de usuarios
$(document).ready(function () {
    $('#usersTable').DataTable().destroy();
    $('#usersTable').DataTable({
        // Configuración de DataTable
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
        "dom": '<"top"Bfl>rt<"bottom"ip><"clear">',
        "buttons": [
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i>',
                className: 'btn btn-danger',
                titleAttr: 'Exportar PDF'
            }
        ],
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todo"]],
        "pageLength": 10
    });
});



