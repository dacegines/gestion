@extends('adminlte::page')

@section('title', 'Administrar Usuarios')

@section('content')
<hr>
<div class="card">
    <div class="card-header-title card-header bg-success text-white text-center">
        <h4 class="card-title-description">Administrar Usuarios</h4>
    </div>
    <div class="card-body text-center">
        {{-- Botón para abrir el modal --}}
        <button type="button" class="btn border" data-toggle="modal" data-target="#createUserModal">
            Crear nuevo usuario
        </button>
        <hr>
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close text-white" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close text-white" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
        
        {{-- Tabla de usuarios --}}
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>Num</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Puesto</th>
                        <th>Rol</th>
                        <th>Permiso</th>
                        <th>Acciones</th> <!-- Mantener para eliminar -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td> <!-- ID del usuario -->
                        <td class="editable" data-id="{{ $user->id }}" data-column="name">{{ $user->user_name }}</td>
                        <td class="editable" data-id="{{ $user->id }}" data-column="email">{{ $user->email }}</td>
                        <td class="editable" data-id="{{ $user->id }}" data-column="puesto">{{ $user->puesto }}</td>
                        <td>{{ $user->role_name }}</td>
                        <td>{{ $user->permission_name }}</td>
                        <td>

                            <!-- Botón para editar -->
                            <button 
                                type="button" 
                                class="btn btn-warning btn-sm edit-user-btn" 
                                data-id="{{ $user->id }}" 
                                data-name="{{ $user->user_name }}" 
                                data-email="{{ $user->email }}" 
                                data-puesto="{{ $user->puesto }}" 
                                data-toggle="modal" 
                                data-target="#editUserModal">
                                Editar
                            </button>

                            <!-- Botón para el modal de Rol -->
                            <button 
                                type="button" 
                                class="btn btn-secondary btn-sm role-btn" 
                                data-id="{{ $user->id }}" 
                                data-name="{{ $user->user_name }}" 
                                data-email="{{ $user->email }}" 
                                data-role="{{ $user->role_id }}" 
                                data-toggle="modal" 
                                data-target="#roleModal">
                                Asignar-Rol
                            </button>
                            
                            
                    
                                <!-- Botón para el modal de Área -->
                            <button 
                                type="button" 
                                class="btn btn-secondary btn-sm area-btn" 
                                data-id="{{ $user->id }}" 
                                data-name="{{ $user->user_name }}" 
                                data-email="{{ $user->email }}" 
                                data-toggle="modal" 
                                data-target="#areaModal">
                                Asignar-Área
                            </button>

                            <!-- Botón para borrar -->
                            <form action="{{ route('adminUsuarios.destroy', $user->id) }}" method="POST" class="d-inline delete-user-form">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm delete-user-btn">
                                    Borrar
                                </button>
                            </form>                   
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
        </div>
    </div>
    
</div>

{{-- Modal --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Crear Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="create-user-form" action="{{ route('adminUsuarios.register') }}" method="post" class="w-100">
                    @csrf

                    {{-- Name field --}}
                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="{{ __('adminlte::adminlte.full_name') }}" autofocus>

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Puesto field --}}
                    <div class="input-group mb-3">
                        <input type="text" name="puesto" class="form-control @error('puesto') is-invalid @enderror"
                               value="{{ old('puesto') }}" placeholder="Puesto">

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-briefcase"></span>
                            </div>
                        </div>

                        @error('puesto')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Email field --}}
                    <div class="input-group mb-3">
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}">
                    
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div id="email-feedback" class="text-danger"></div>

                    {{-- Password field --}}
                    <div class="input-group mb-3">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="{{ __('adminlte::adminlte.password') }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>                            
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>

                    </div>
                    <div id="password-requirements" class="text-muted mt-1 d-none">
                        <small id="length" class="text-danger">La contraseña debe tener al menos 8 caracteres.</small><br>
                        <small id="uppercase" class="text-danger">La contraseña debe incluir una letra mayúscula.</small><br>
                        <small id="number" class="text-danger">La contraseña debe incluir un número.</small><br>
                        <small id="special" class="text-danger">La contraseña debe incluir un carácter especial (!@#$%^&*).</small>
                    </div>


                    {{-- Confirm password field --}}
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" id="password-confirm" class="form-control"
                            placeholder="{{ __('adminlte::adminlte.retype_password') }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#password-confirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>                            
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>

                    </div>
                    <div id="password-feedback" class="mt-1"></div>


                    {{-- Register button --}}
                    <div class="d-flex justify-content-center">
                        <button type="submit" id="submit-btn" class="btn btn-block btn-success w-25 text-center" style="display: none;">
                            <span class="fas fa-user-plus"></span>
                            Crear nuevo usuario
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-user-form" action="{{ route('adminUsuarios.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="editUserId">

                    {{-- Nombre --}}
                    <div class="form-group">
                        <label for="editUserName">Nombre</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required>
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="editUserEmail">Email</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>

                    {{-- Puesto --}}
                    <div class="form-group">
                        <label for="editUserPuesto">Puesto</label>
                        <input type="text" name="puesto" id="editUserPuesto" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Rol -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">Gestionar Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true" class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="roleForm" action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <!-- Input oculto predefinido -->
                    <input type="hidden" name="model_type" value="App\Models\User">
                    
                    <!-- Input oculto para el ID del usuario -->
                    <input type="hidden" name="model_id" id="modelIdRoleInput">
                
                    <!-- Input visible para mostrar nombre y email -->
                    <div class="form-group">
                        <label for="userNameEmailRoleInput">Usuario</label>
                        <input type="text" id="userNameEmailRoleInput" class="form-control" readonly>
                    </div>
                
                    <!-- Select para roles -->
                    <div class="form-group">
                        <label for="roleSelect">Seleccionar Rol</label>
                        <select id="roleSelect" name="role_id" class="form-control">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Área -->
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="areaModalLabel">Gestionar Área</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="areaForm" action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    <!-- Input oculto predefinido -->
                    <input type="hidden" name="model_type" value="App\Models\User">
                    
                    <!-- Input oculto para el ID del usuario -->
                    <input type="hidden" name="model_id" id="modelIdInput">

                    <!-- Input visible para mostrar nombre y email -->
                    <div class="form-group">
                        <label for="userNameEmailInput">Usuario</label>
                        <input type="text" id="userNameEmailInput" class="form-control" readonly>
                    </div>
                
                    <!-- Select para permisos -->
                    <div class="form-group">
                        <label for="areaSelect">Seleccionar Permiso</label>
                        <select id="areaSelect" name="permission_id" class="form-control">
                            @foreach ($permissions as $permission)
                                <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                            @endforeach
                        </select>
                    </div>
                
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>





@endsection

@section('js')

<script>
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

</script>

<script>
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
</script>



<script>
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
</script>



<script>
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
</script>


<script>
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


</script>

<script>
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
</script>

<script>
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
</script>

<script>
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


</script>



<script>
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
</script>

<script>
    $(document).ready(function () {
    // Manejar los botones para mostrar/ocultar contraseñas
    $('.toggle-password').on('click', function () {
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
});
</script>

<script>
$(document).ready(function () {
    $('#usersTable').DataTable({
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


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.3/parsley.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>


@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="{{ asset('css/detalles/styles.css')}}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@stop
