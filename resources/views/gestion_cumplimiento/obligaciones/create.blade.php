@extends('adminlte::page')

@section('title', 'Agregar Obligación')

@section('content_header')

@stop

@section('content')
<br>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulario para agregar una nueva obligación</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">

                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre de la Obligación</label>
                    <input type="text" name="nombre" class="form-control" id="nombre" placeholder="Ingrese el nombre">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" class="form-control" id="descripcion" placeholder="Ingrese la descripción"></textarea>
                </div>
                <div class="form-group">
                    <label for="responsable">Responsable</label>
                    <input type="text" name="responsable" class="form-control" id="responsable" placeholder="Ingrese el responsable">
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/obligaciones/styles.css')}}"> 
@stop