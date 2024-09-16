@extends('adminlte::page')

@section('title', 'Resumen')

{{--@section('content_header')
    <h1>Resumen</h1>
@stop--}}


@section('content')
<br>
<div class="card" id="card-resumen">
        <div class="card-header bg-success text-white text-center">
                <h3 class="">Resumen</h3>
            </div>
                    <div class="card-body">
                    <div class="col-md-2">



                        </div>
                        <hr class="divider">

                        <div class="row text-center justify-content-center">
                        <div class="col-md-2 mb-3 custom-size">
                        <div class="card metric-card">
                            <div class="card-body">
                                <i class="fas fa-tasks fa-3x text-primary"></i>
                                <h5 class="card-title">Obligaciones:</h5>
                                <p id="total_obligaciones" class="card-text display-4 font-weight-bold">{{ $totalObligaciones }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#detailsModal" data-title="Obligaciones" onclick="cargarDetalles('activas')">Ver Detalles</a>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <i class="fas fa-comments fa-3x text-info"></i>
                                <h5 class="card-title">Activas:</h5>
                                <p id="activas" class="card-text display-4 font-weight-bold">{{ $activas }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#detailsModalA" data-title="Obligaciones" onclick="cargarDetalles('activas')">Ver Detalles</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <i class="fas fa-check fa-3x text-success"></i>
                                <h5 class="card-title">Completas:</h5>
                                <p id="completas" class="card-text display-4 font-weight-bold">{{ $completas }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#detailsModalC" data-id="">Ver Detalles</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-3x text-danger"></i>
                                <h5 class="card-title">Vencidas:</h5>
                                <p id="vencidas" class="card-text display-4 font-weight-bold">{{ $vencidas }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#detailsModalV" data-id="">Ver Detalles</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3">
                        <div class="card metric-card">
                            <div class="card-body">
                                <i class="fas fa-clock fa-3x text-warning"></i>
                                <h5 class="card-title">Por vencer:</h5>
                                <p id="por_vencer" class="card-text display-4 font-weight-bold">{{ $porVencer }}</p>
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#detailsModalP" data-id="">Ver Detalles</a>
                            </div>
                        </div>
                    </div>

                        </div>
                        <hr class="divider">

                        <div class="row" id="chartContainer">
                            <div class="col-md-12">
                                <div class="report-card" style="width: 100%;">
                                    <div class="card-body">
                                        <h5>Estatus General</h5>
                                        <canvas id="vencidasPorVencerCompletasChart" style="width: 100%;"></canvas>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones</h5>
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
                                    @foreach($requisitos as $index => $requisito)
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

                    <div class="modal fade" id="detailsModalA" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones - Activas</h5>
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
                                    @foreach($requisitosActivos as $index => $requisito)
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



                <div class="modal fade" id="detailsModalC" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones - Completas</h5>
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
                                    @foreach($requisitosCompletos as $index => $requisito)
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

                    <div class="modal fade" id="detailsModalV" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones - Vencidas</h5>
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
                                    @foreach($requisitosVencidos as $index => $requisito)
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

                     <div class="modal fade" id="detailsModalV" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones - Por vences</h5>
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
                                    @foreach($requisitosVencidos as $index => $requisito)
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
                    
                    <div class="modal fade" id="detailsModalP" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Obligaciones</h5>
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
                                    @foreach($requisitosPorVencer as $index => $requisito)
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
                    
                    
                    

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    <link rel="stylesheet" href="{{ asset('css/resumen/styles.css')}}"> 
@stop

@section('js')
<script src="{{ asset('js/js/resumen/main.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.chartData = {
            fechas: {!! json_encode($fechas) !!},
            vencidas: {!! json_encode($vencidasG) !!},
            porVencer: {!! json_encode($porVencerG) !!},
            completas: {!! json_encode($completasG) !!}
        };
    </script>

@stop