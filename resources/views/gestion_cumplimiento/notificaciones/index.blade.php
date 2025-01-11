@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
    <hr>
    <div class="card">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Administrar Notificaciones</h4>
        </div>

        <div class="card-body">
            <hr>
            
        </div>
    </div>



@endsection

@section('js')

@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@stop
