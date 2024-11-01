@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@section('auth_header', 'Solicitud de Cuenta Nueva')

@section('auth_body')
    <form action="{{ route('custom.account.register.submit') }}" method="post">
    <!-- Cambia esta línea para que use la ruta correcta -->
        @csrf

        {{-- Nombre --}}
        <div class="input-group mb-3">
            <input type="text" name="name" class="form-control" placeholder="Nombre Completo" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user" style="color: white;"></span>
                </div>
            </div>
        </div>

        {{-- Puesto --}}
        <div class="input-group mb-3">
            <input type="text" name="puesto" class="form-control" placeholder="Puesto" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-briefcase" style="color: white;"></span>
                </div>
            </div>
        </div>

        {{-- Correo Electrónico --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Correo Electrónico" required>
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
        <p class="text-info mb-3" style="color: white !important; font-size: 0.9rem; text-align: center;">
            Esta información será enviada al administrador del sistema para proceder con la creación de la cuenta.
        </p>

        {{-- Botón de Registro --}}
        <button type="submit" class="btn btn-block btn-success">
            Solicitar Cuenta Nueva
        </button>

    </form>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ $login_url }}">
            Ya tengo una cuenta
        </a>
    </p>
@stop
@section('adminlte_js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('status'))
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
    @endif
@stop

@section('adminlte_css')
    <style>
        /* Ajuste del estilo de los inputs */
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
    </style>
@stop
