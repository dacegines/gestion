@extends('adminlte::page')

@section('title', 'Resumen')

@section('content')
<hr>
<div class="card" id="card-resumen">
    <div class="card-header-title card-header bg-success text-white text-center">
        <h4 class="card-title-description">Resumen</h4>
    </div>
    <div class="card-body">
        <form id="filter-form" action="{{ route('filtrar-requisitos') }}" method="POST" class="form-inline d-flex align-items-center justify-content-center">
            @csrf
            <label for="year-select" class="mr-2">Año:</label>
            <select id="year-select" name="year" class="form-control form-control-sm">
                @for ($yearOption = 2024; $yearOption <= 2040; $yearOption++)
                <option value="{{ $yearOption }}" {{ isset($year) && $year == $yearOption ? 'selected' : '' }}>
                    {{ $yearOption }}
                </option>
                @endfor
            </select>
            <button type="submit" class="btn btn-success btn-sm ml-2">Ver</button>
        </form>
        <hr class="divider">
        <div class="row text-center justify-content-center">
            @foreach ([
                ['icon' => 'fa-tasks', 'color' => 'primary', 'title' => 'Obligaciones', 'id' => 'total_obligaciones', 'value' => e($totalObligaciones), 'modal' => 'detailsModal'],
                ['icon' => 'fa-comments', 'color' => 'info', 'title' => 'Activas', 'id' => 'activas', 'value' => e($activas), 'modal' => 'detailsModalA'],
                ['icon' => 'fa-check', 'color' => 'success', 'title' => 'Completas', 'id' => 'completas', 'value' => e($completas), 'modal' => 'detailsModalC'],
                ['icon' => 'fa-times-circle', 'color' => 'danger', 'title' => 'Vencidas', 'id' => 'vencidas', 'value' => e($vencidas), 'modal' => 'detailsModalV'],
                ['icon' => 'fa-clock', 'color' => 'warning', 'title' => 'Por vencer', 'id' => 'por_vencer', 'value' => e($porVencer), 'modal' => 'detailsModalP']
            ] as $card)
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="card-total-detail card-header bg-light text-white text-center">
                        <i class="icon-card-resumen fas {{ e($card['icon']) }} fa-3x text-{{ e($card['color']) }}"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ e($card['title']) }}:</h5>
                        <p id="{{ e($card['id']) }}" class="card-text display-4 font-weight-bold">{{ e($card['value']) }}</p>
                        <a href="#" class="btn btn-link" data-toggle="modal" data-target="#{{ e($card['modal']) }}"><b>Ver Detalles</b></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header-title card-header bg-success text-white text-center">
                        <h5 class="card-title-description">Avance de Obligaciones</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="barChartObligaciones"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header-title card-header bg-success text-white text-center">
                        <h5 class="card-title-description">Avance Total</h5>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="avanceTotalChart" style="width: 100%; height: 228px;"></canvas>
                    </div>
                    <div class="card-body" id="tablasPeriodicidad"></div>
                </div>
            </div>
        </div>
        <div class="row" id="chartContainer">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header-title card-header bg-success text-white text-center">
                        <h5 class="card-title-description">Estatus General</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vencidasPorVencerCompletasChart" style="width: 100%; height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach([
    ['id' => 'detailsModal', 'title' => 'Obligaciones', 'requisitos' => $requisitos],
    ['id' => 'detailsModalA', 'title' => 'Obligaciones - Activas', 'requisitos' => $requisitosActivos],
    ['id' => 'detailsModalC', 'title' => 'Obligaciones - Completas', 'requisitos' => $requisitosCompletos],
    ['id' => 'detailsModalV', 'title' => 'Obligaciones - Vencidas', 'requisitos' => $requisitosVencidos],
    ['id' => 'detailsModalP', 'title' => 'Obligaciones - Por Vencer', 'requisitos' => $requisitosPorVencer],
] as $modal)
<div class="modal fade" id="{{ e($modal['id']) }}" tabindex="-1" role="dialog" aria-labelledby="{{ e($modal['id']) }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ e($modal['id']) }}Label">{{ e($modal['title']) }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th>#Registros</th>
                                <th>Obligación</th>
                                <th>Responsable</th>
                                <th>Periodicidad</th>
                                <th>Fecha Límite</th>
                            </tr>
                        </thead>
                        <tbody id="modalContent">
                            @foreach($modal['requisitos'] as $index => $requisito)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ e($requisito->evidencia) }}</td>
                                <td>{{ e($requisito->responsable) }}</td>
                                <td>{{ e($requisito->periodicidad) }}</td>
                                <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d-m-Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/resumen/styles.css') }}">
@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
window.chartData = {
    fechas: @json($fechas),
    vencidas: @json($vencidasG),
    porVencer: @json($porVencerG),
    completas: @json($completasG)
};
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('barChartObligaciones').getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($nombres),
            datasets: [{
                label: 'Total Avance',
                data: @json($avancesTotales),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    grid: { display: true },
                    ticks: { callback: function(value) { return value + '%'; } }
                },
                y: { grid: { display: true } }
            },
            plugins: {
                legend: { display: true, position: 'top' },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: function(value) { return value.toFixed(2) + '%'; },
                    color: 'black',
                    font: { weight: 'bold' }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    new Chart(document.getElementById('avanceTotalChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Avance', 'Restante'],
            datasets: [{
                data: [{{ $porcentajeAvance }}, 100 - {{ $porcentajeAvance }}],
                backgroundColor: ['#36a2eb', '#d3d3d3'],
                hoverBackgroundColor: ['#36a2eb', '#d3d3d3']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(tooltipItem) { return tooltipItem.label + ': ' + tooltipItem.raw + '%'; } } },
                datalabels: {
                    display: true,
                    formatter: function(value, context) { return context.dataIndex === 0 ? value + '%' : ''; },
                    color: '#000000',
                    font: { size: '30', weight: 'bold' },
                    anchor: 'center',
                    align: 'center'
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    new Chart(document.getElementById('vencidasPorVencerCompletasChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: window.chartData.fechas,
            datasets: [
                {
                    label: 'Vencidas',
                    data: window.chartData.vencidas,
                    borderColor: '#ff6384',
                    backgroundColor: 'transparent',
                    fill: false
                },
                {
                    label: 'Por Vencer',
                    data: window.chartData.porVencer,
                    borderColor: '#ffcd56',
                    backgroundColor: 'transparent',
                    fill: false
                },
                {
                    label: 'Completas',
                    data: window.chartData.completas,
                    borderColor: '#4bc0c0',
                    backgroundColor: 'transparent',
                    fill: false
                }
            ]
        },
        options: {
            scales: {
                x: { beginAtZero: true, grid: { display: true } },
                y: { beginAtZero: true, grid: { display: true } }
            },
            plugins: {
                legend: { display: true, position: 'top' }
            }
        }
    });
    document.getElementById('tablasPeriodicidad').innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr><th>Periodo</th><th>Avance (%)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>{{ e($bimestral->periodicidad ?? 'Bimestral') }}</td><td>{{ e($bimestral->avance ?? 0) }}%</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr><th>Periodo</th><th>Avance (%)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>{{ e($semestral->periodicidad ?? 'Semestral') }}</td><td>{{ e($semestral->avance ?? 0) }}%</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4">
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr><th>Periodo</th><th>Avance (%)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>{{ e($anual->periodicidad ?? 'Anual') }}</td><td>{{ e($anual->avance ?? 0) }}%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>`;
});
</script>
@endsection
