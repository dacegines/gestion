@extends('adminlte::page')

@section('title', 'Inicio')

@section('content')
    <div class="container">
        <div class="row align-items-center" style="min-height: 80vh;">
            {{-- Columna para el texto --}}
            <div class="col-md-6 text-center text-md-start">
                <h1 class="text-success fw-bold">¡Bienvenido a Seguimiento de Obligaciones TDC!</h1>
                <p class="lead mt-4">
                    Bienvenido al Sistema de Control y Seguimiento de Obligaciones.
                </p>
                <p class="lead mt-4">
                    Diseñado para gestionar y monitorear eficientemente las obligaciones del título de concesión de la
                    SuperVía.
                </p>
                <p class="mt-3">
                    Esta plataforma ofrece herramientas para garantizar el cumplimiento de obligaciones, centralizar
                    información y facilitar la colaboración entre equipos.
                </p>

            </div>

            {{-- Columna para la imagen --}}
            <div class="col-md-6 text-center">
                <img src="{{ asset('img/superva_poniente_cover.jpg') }}" alt="Bienvenida" class="img-fluid img_ini"
                    style="max-width: 90%; border-radius: 15px;">
            </div>
        </div>
    </div>
@endsection

@section('css')

    <style>
        h1 {
            font-size: 2.5rem;
        }

        .img_ini {
            animation: zoomIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Estilo general para todos los enlaces del menú */
        .sidebar .nav-link {
            font-size: 0.88rem;
            /* Tamaño uniforme para el texto */
            color: #ccc;
            /* Color del texto */
        }

        /* Iconos dentro de los enlaces */
        .sidebar .nav-link i {
            margin-right: 0.0rem;
            /* Espaciado entre ícono y texto */
        }

        /* Responsividad para pantallas más pequeñas */
        @media (max-width: 768px) {
            .sidebar .nav-link {
                font-size: 0.85rem;
                /* Reducir tamaño del texto en pantallas pequeñas */
            }

            .sidebar .nav-link i {
                font-size: 0.9rem;
                /* Reducir tamaño de los íconos en pantallas pequeñas */
            }
        }
    </style>

@stop
