@extends('adminlte::page')

@section('title', 'Detalles')

{{--@section('content_header')
    <h1>Detalles</h1>
@stop--}}

@section('content')
<br>
<div class="card">
            <div class="card-header bg-success text-white text-center">
                    <h3 class="">Detalles</h3>
                </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form class="form-row align-items-end">
                                <div class="form-group col-md-2">
                                    <label for="startDate">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="startDate">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="endDate">Fecha de fin</label>
                                    <input type="date" class="form-control" id="endDate">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="sortOrder">Ordenar por</label>
                                    <select class="form-control" id="sortOrder">
                                        <option value="">Seleccione</option>
                                        <option value="numReqAsc">Número de requisito Ascendente</option>
                                        <option value="numReqDesc">Número de requisito Descendente</option>
                                        <option value="fechaAsc">Fecha límite Ascendente</option>
                                        <option value="fechaDesc">Fecha límite Descendente</option>
                                        <option value="vencido">Vencido</option>
                                        <option value="activo">Activo</option>
                                        <option value="proximoVencer">Próximo a vencer</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-success btn-block">Filtrar</button>
                                </div>
                                <div class="form-group col-md-2">
                                    <button id="resetFilters" class="btn btn-secondary btn-block">Resetear filtros</button>
                                </div>
                            </form>
                        </div>
                        <div class="divider"></div>
                        <h5 class="card-title">Vencimientos próximos</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover text-center">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Número de requisito</th>
                                        <th scope="col">Obligación</th>
                                        <th scope="col">Periodicidad</th>
                                        <th scope="col">Número de evidencia</th>
                                        <th scope="col">Avance</th>
                                        <th scope="col">Fecha límite de cumplimiento</th>
                                        <th scope="col">Responsable</th>
                                        <th scope="col">Estatus</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="t_vencimientos">
                                    <!-- El contenido se llenará dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalVer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detalle de Evidencia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Aquí van los detalles del vencimiento -->
                    <div id="modalContent">Cargando...</div>
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
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css')}}"> 
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop