@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', 'Recuperar Contraseña')

@section('auth_body')
    <form action="{{ route('custom.password.reset.submit') }}" method="post">
        @csrf

        {{-- Nombre --}}
        <div class="input-group mb-3">
            <select name="name" class="form-control" required>
                <option value="">Seleccione Nombre</option>
                @foreach($names as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user" style="color: white;"></span>
                </div>
            </div>
        </div>

        {{-- Puesto --}}
        <div class="input-group mb-3">
            <select name="puesto" class="form-control" required>
                <option value="">Seleccione Puesto</option>
                @foreach($positions as $position)
                    <option value="{{ $position }}">{{ $position }}</option>
                @endforeach
            </select>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-briefcase" style="color: white;"></span>
                </div>
            </div>
        </div>

        {{-- Correo Electrónico --}}
        <div class="input-group mb-3">
            <select name="email" class="form-control @error('email') is-invalid @enderror" required>
                <option value="">Seleccione Correo Electrónico</option>
                @foreach($emails as $email)
                    <option value="{{ $email }}">{{ $email }}</option>
                @endforeach
            </select>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope" style="color: white;"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Texto informativo --}}
        <p class="text-info mb-3" style="color: white; font-size: 0.9rem;">
            Esta información será enviada al administrador del sistema para proceder con la recuperación de su contraseña.
        </p>

        {{-- Botón de envío --}}
        <button type="submit" class="btn btn-block btn-success">
            Solicitar Recuperación de Contraseña
        </button>

    </form>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ $login_url }}">
            Volver al inicio de sesión
        </a>
    </p>
@stop

@section('adminlte_js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if(session('status'))
            Swal.fire({
                title: 'Correo Enviado Exitosamente',
                text: 'El administrador del sistema se pondrá en contacto con usted lo más pronto posible.',
                icon: 'success',
                timer: 8000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif
    </script>
@stop

@section('adminlte_css')
    <style>
        /* Ajuste del estilo de los selects */
        .input-group {
            margin-bottom: 1rem;
        }
        .input-group .form-control {
            border-radius: 6px;
        }
        .input-group-append .input-group-text {
            background-color: #e9ecef;
            border-radius: 6px;
        }
        /* Ajustes de texto */
        .text-info {
            color: white !important;
            font-size: 0.9rem;
            text-align: center;
        }
        /* Centrando el formulario */
        .card {
            padding: 0px;
        }
    </style>
@stop
