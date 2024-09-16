@extends('adminlte::page')

@section('title', 'Resumen')

@section('content')
<br>
<div class="card" id="card-resumen">
    <div class="card-header bg-success text-white text-center">
        <h3 class="">Resumen</h3>
    </div>
    <div class="card-body">
        
        <hr class="divider">
        
        {{-- TARJETAS DE RESUMEN --}}
        <div class="row text-center justify-content-center mt-4">
            @foreach ([
                ['icon' => 'fa-tasks', 'color' => 'primary', 'title' => 'Obligaciones', 'id' => 'total_obligaciones', 'value' => $totalObligaciones, 'modal' => 'detailsModal'],
                ['icon' => 'fa-comments', 'color' => 'info', 'title' => 'Activas', 'id' => 'activas', 'value' => $activas, 'modal' => 'detailsModalA'],
                ['icon' => 'fa-check', 'color' => 'success', 'title' => 'Completas', 'id' => 'completas', 'value' => $completas, 'modal' => 'detailsModalC'],
                ['icon' => 'fa-times-circle', 'color' => 'danger', 'title' => 'Vencidas', 'id' => 'vencidas', 'value' => $vencidas, 'modal' => 'detailsModalV'],
                ['icon' => 'fa-clock', 'color' => 'warning', 'title' => 'Por vencer', 'id' => 'por_vencer', 'value' => $porVencer, 'modal' => 'detailsModalP']
            ] as $card)
                <div class="col-md-2 mb-3">
                    <div class="card metric-card">
                        <div class="card-header bg-light text-white text-center"> <!-- Color verde para el encabezado -->
                            <i class="fas {{ $card['icon'] }} fa-3x text-{{ $card['color'] }}"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $card['title'] }}:</h5>
                            <p id="{{ $card['id'] }}" class="card-text display-4 font-weight-bold">{{ $card['value'] }}</p>
                            <a href="#" class="btn btn-link" data-toggle="modal" data-target="#{{ $card['modal'] }}">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="divider">

        {{-- NUEVAS SECCIONES --}}
        <div class="row">
            <div class="col-md-6">
                <!-- Tarjeta de Bootstrap para la gráfica de "Resumen de Obligaciones" -->
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h5>Avance de Obligaciones</h5> <!-- Título de la tarjeta -->
                    </div>
                    <div class="card-body">
                        <!-- Contenedor del canvas para Chart.js -->
                        <canvas id="barChartObligaciones"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
    <!-- Tarjeta para la gráfica de anillo de avance total -->
    <div class="card">
        <div class="card-header bg-success text-white text-center">
            <h5>Avance Total</h5> <!-- Título de la tarjeta -->
        </div>
        <div class="card-body text-center">
            <!-- Contenedor del canvas para Chart.js -->
            <canvas id="avanceTotalChart" style="width: 100%; height: 228px;"></canvas>
        </div>
        <!-- Contenedor para las tablas de periodicidad -->
        <div class="card-body" id="tablasPeriodicidad">
            <!-- Las tablas se generarán aquí dinámicamente -->
        </div>
    </div>
</div>


        </div>

        <hr class="divider">

        {{-- GRÁFICA GENERAL --}}
        <div class="row" id="chartContainer">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h5>Estatus General</h5>
                    </div>
                    <div class="card-body">
                        <!-- Reducir más la altura de la gráfica -->
                        <canvas id="vencidasPorVencerCompletasChart" style="width: 100%; height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales para Detalles --}}
@foreach([
    ['id' => 'detailsModal', 'title' => 'Obligaciones', 'requisitos' => $requisitos],
    ['id' => 'detailsModalA', 'title' => 'Obligaciones - Activas', 'requisitos' => $requisitosActivos],
    ['id' => 'detailsModalC', 'title' => 'Obligaciones - Completas', 'requisitos' => $requisitosCompletos],
    ['id' => 'detailsModalV', 'title' => 'Obligaciones - Vencidas', 'requisitos' => $requisitosVencidos],
    ['id' => 'detailsModalP', 'title' => 'Obligaciones - Por Vencer', 'requisitos' => $requisitosPorVencer],
] as $modal)
<div class="modal fade" id="{{ $modal['id'] }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modal['id'] }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modal['id'] }}Label">{{ $modal['title'] }}</h5>
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
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $requisito->evidencia }}</td>
                                    <td>{{ $requisito->responsable }}</td>
                                    <td>{{ $requisito->periodicidad }}</td>
                                    <td>{{ \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d-m-Y') }}</td>
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
<script src="{{ asset('js/js/resumen/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>



<script>
    window.chartData = {
        fechas: {!! json_encode($fechas) !!},
        vencidas: {!! json_encode($vencidasG) !!},
        porVencer: {!! json_encode($porVencerG) !!},
        completas: {!! json_encode($completasG) !!}
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Realizar la petición POST con Axios
        axios.post('/api/resumen-obligaciones', {})
            .then(function (response) {
                const resumenRequisitos = response.data;

                const nombres = resumenRequisitos.map(obligacion => obligacion.nombre);
                const avancesTotales = resumenRequisitos.map(obligacion => parseFloat(obligacion.total_avance));

                const ctx = document.getElementById('barChartObligaciones').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Total Avance',
                            data: avancesTotales,
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
                                grid: {
                                    display: true
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: true
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'end',
                                formatter: function(value) {
                                    return value.toFixed(2) + '%';
                                },
                                color: 'black',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            })
            .catch(function (error) {
                console.error('Error al obtener los datos:', error);
            });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Realizar la petición POST con Axios para obtener el avance total
    axios.post('/api/obtener-avance-total', {})
        .then(function (response) {
            const data = response.data;

            // Datos para la gráfica de anillo
            const avance = data.avance;
            const restante = 100 - avance;

            // Crear la gráfica de anillo
            const ctx = document.getElementById('avanceTotalChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Avance', 'Restante'],
                    datasets: [{
                        data: [avance, restante],
                        backgroundColor: ['#36a2eb', '#d3d3d3'],  // Colores para el avance y el restante
                        hoverBackgroundColor: ['#36a2eb', '#d3d3d3']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + '%';
                                }
                            }
                        },
                        datalabels: {
                            display: true,
                            formatter: function (value, context) {
                                // Mostrar solo el porcentaje de avance en el centro
                                if (context.dataIndex === 0) {  // Solo muestra el valor de 'Avance'
                                    return avance + '%';
                                } else {
                                    return '';
                                }
                            },
                            color: '#000000',  // Cambiar el color del texto a negro
                            font: {
                                size: '30',
                                weight: 'bold'
                            },
                            anchor: 'center',
                            align: 'center'
                        }
                    }
                },
                plugins: [ChartDataLabels]  // Habilitar el plugin de etiquetas
            });
        })
        .catch(function (error) {
            console.error('Error al obtener los datos del avance total:', error);
        });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Realizar la petición POST con Axios para obtener el avance total por periodicidad
    axios.post('/api/obtener-avance-periodicidad', {})
        .then(function (response) {
            const data = response.data;

            // Desestructurar los datos de respuesta
            const { bimestral, semestral, anual } = data;

            // Crear tablas dinámicamente
            const bimestralTable = `
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Avance (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${bimestral.periodicidad}</td>
                            <td>${bimestral.avance || 0}%</td>
                        </tr>
                    </tbody>
                </table>
            `;

            const semestralTable = `
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Avance (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${semestral.periodicidad}</td>
                            <td>${semestral.avance || 0}%</td>
                        </tr>
                    </tbody>
                </table>
            `;

            const anualTable = `
                <table class="table table-bordered text-center" style="font-size: 0.8rem;">
                    <thead class="thead-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Avance (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${anual.periodicidad}</td>
                            <td>${anual.avance || 0}%</td>
                        </tr>
                    </tbody>
                </table>
            `;

            // Insertar las tablas en el HTML
            document.getElementById('tablasPeriodicidad').innerHTML = `
                <div class="row">
                    <div class="col-md-4">${bimestralTable}</div>
                    <div class="col-md-4">${semestralTable}</div>
                    <div class="col-md-4">${anualTable}</div>
                </div>
            `;
        })
        .catch(function (error) {
            console.error('Error al obtener los datos de avance por periodicidad:', error);
        });
});
</script>




<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


@endsection
