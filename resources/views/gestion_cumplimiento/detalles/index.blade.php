@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
<hr>
<div class="card">
    <div class="card-header-title card-header bg-success text-white text-center">
        <h4 class="card-title-description">Detalles</h4>
    </div>

    <div class="card-body">
        <div class="row justify-content-center">
            <form action="{{ route('filtrar-detalle') }}" method="POST" class="form-inline d-flex align-items-center">
                @csrf
                <div class="form-group mr-2">
                    <label for="year-select" class="mr-2">Año:</label>
                    <select id="year-select" name="year" class="form-control form-control-sm">
                        @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                            <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-sm">Ver</button>
            </form>
        </div>

        <div class="divider"></div>

        <div class="table-responsive">
            <table id="detallesTable" class="table table-striped table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>#Num</th>
                        <th>#Requisito</th>
                        <th>Cláusula</th>
                        <th>Obligación</th>
                        <th>Periodicidad</th>
                        <th>#Obligación</th>
                        <th>Avance</th>
                        <th>Fecha límite</th>
                        <th>Responsable</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Requisitos filtrados --}}
                    @foreach($requisitos as $requisito)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ htmlspecialchars($requisito->numero_requisito, ENT_QUOTES, 'UTF-8') }}</td>
                            <td>{{ htmlspecialchars($requisito->clausula_condicionante_articulo, ENT_QUOTES, 'UTF-8') }}</td>
                            <td>{{ htmlspecialchars($requisito->evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                            <td>{{ htmlspecialchars($requisito->periodicidad, ENT_QUOTES, 'UTF-8') }}</td>
                            <td>{{ htmlspecialchars($requisito->numero_evidencia, ENT_QUOTES, 'UTF-8') }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-{{ $requisito->porcentaje === 100 ? 'success' : ($requisito->porcentaje < 100 && \Carbon\Carbon::now()->gt($requisito->fecha_limite_cumplimiento) ? 'danger' : 'info') }}" 
                                         role="progressbar" style="width: {{ $requisito->porcentaje }}%;" 
                                         aria-valuenow="{{ $requisito->porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ htmlspecialchars($requisito->porcentaje, ENT_QUOTES, 'UTF-8') }}%
                                    </div>
                                </div>
                            </td>
                            <td style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}
                            </td>
                            <td>{{ htmlspecialchars($requisito->responsable, ENT_QUOTES, 'UTF-8') }}</td>
                            <td><span class="badge badge-{{ $requisito->porcentaje === 100 ? 'success' : (\Carbon\Carbon::now()->gt($requisito->fecha_limite_cumplimiento) ? 'danger' : (\Carbon\Carbon::now()->diffInDays($requisito->fecha_limite_cumplimiento, false) <= 30 ? 'warning' : 'info')) }}">
                                {{ $requisito->porcentaje === 100 ? 'Cumplido' : (\Carbon\Carbon::now()->gt($requisito->fecha_limite_cumplimiento) ? 'Vencido' : (\Carbon\Carbon::now()->diffInDays($requisito->fecha_limite_cumplimiento, false) <= 30 ? 'Próximo a Vencer' : 'Activo')) }}
                            </span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection


@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css')}}">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Enlace al archivo JavaScript movido -->
    <script src="{{ asset('js/gestion_obligaciones/detalles/detalles.js') }}"></script>
@stop
