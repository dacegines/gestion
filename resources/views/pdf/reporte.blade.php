<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Detalles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        /* Encabezado para todas las páginas */
        @page {
            margin-top: 120px; /* Asegura espacio para el encabezado */
            margin-bottom: 50px;
        }

        header {
            position: fixed;
            top: -80px; /* Ajustar para que aparezca al principio de cada página */
            left: 0;
            right: 0;
            height: 80px;
            text-align: left;
            padding-left: 20px;
        }

        .header-logo img {
            width: 150px;
        }

        h2 {
            text-align: center;
            margin-top: 0;
        }

        .table {
            width: 93.6%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table, .table th, .table td {
            border: 1px solid black;
        }

        .table th, .table td {
            padding: 8px;
            text-align: center;
            font-size: 9px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .thead-dark {
            background-color: #343a40;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 5px;
            border-radius: 5px;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 5px;
            border-radius: 5px;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo que aparecerá en cada página -->
    <header>
        <div class="header-logo">
            <img src="{{ public_path('img/logo_supervia.png') }}" alt="Logo">
        </div>
        <h2>Reporte de Detalles del Año {{ $year }}</h2>
    </header>

    <!-- Tabla de detalles -->
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Cláusula</th>
                <th>Obligación</th>
                <th>Periodicidad</th>
                <th>Adjuntos</th>
                <th>Fecha límite</th>
                <th>Responsable</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitos as $requisito)
            <tr>
                <td>{{ $requisito->numero_evidencia }}</td>
                <td style="text-align: justify;">{{ $requisito->clausula }}</td>
                <td style="text-align: justify;">{{ $requisito->requisito_evidencia }}</td>
                <td>{{ $requisito->periodicidad }}</td>
                <td>
                    @if($requisito->cantidad_archivos > 0)
                        {{ $requisito->cantidad_archivos }} archivo{{ $requisito->cantidad_archivos > 1 ? 's' : '' }}
                    @else
                        No hay adjuntos
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}</td>
                <td>{{ $requisito->responsable }}</td>
                <td>
                    <span class="badge badge-{{ $requisito->estatus === 'Cumplido' ? 'success' : ($requisito->estatus === 'Vencido' ? 'danger' : 'warning') }}">
                        {{ $requisito->estatus }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
