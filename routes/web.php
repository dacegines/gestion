<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ObligacionesController;

use App\Http\Controllers\UsuarioController;

use App\Http\Controllers\ArchivoController;

use App\Http\Controllers\DetallesController;

use App\Http\Controllers\ResumenController;

use App\Http\Controllers\DashboardController;



Route::get('/', function () {
    return view('auth.login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [UsuarioController::class, 'profile']);


});


//ruta de obligaciones
Route::get('/obligaciones', [ObligacionesController::class, 'index'])->name('obligaciones');

Route::match(['get', 'post'], '/detalles', [DetallesController::class, 'index'])->name('gestion_cumplimiento.detalles.index');

// En routes/web.php
Route::post('/obtener-detalles', [ObligacionesController::class, 'getDetallesEvidencia'])->name('obtener.detalles');
Route::post('/obtener-notificaciones', [ObligacionesController::class, 'obtenerNotificaciones'])->name('obtener.notificaciones');
Route::post('/obtener-tabla-notificaciones', [ObligacionesController::class, 'obtenerTablaNotificaciones'])->name('obtener.tabla.notificaciones');
Route::post('/requisito/cambiar-estado', [ObligacionesController::class, 'cambiarEstado'])->name('requisito.cambiarEstado');
Route::post('/obtener-detalle-evidencia', [ObligacionesController::class, 'obtenerDetalleEvidencia'])->name('obtener.detalle.evidencia');  
Route::post('/approved-result', [ObligacionesController::class, 'obtenerEstadoApproved'])->name('approved.resul');


// envio de correo al subir archivo
Route::post('/ruta-enviar-correo-datos-evidencia', [ObligacionesController::class, 'enviarCorreoDatosEvidencia'])->name('enviar.correo.datos.evidencia');

// cambiar estatus de obligacion
Route::post('/obligaciones/verificar-archivos', [ObligacionesController::class, 'verificarArchivos'])->name('obligaciones.verificarArchivos');



// porcentaje de avance
Route::post('/actualizar-porcentaje', [ObligacionesController::class, 'actualizarPorcentaje'])->name('actualizar.porcentaje');

Route::post('/actualizar-suma-porsentaje', [ObligacionesController::class, 'actualizarPorcentajeSuma'])->name('actualizar.suma.porcentaje');










Route::get('/AddObligaciones', [ObligacionesController::class, 'store'])->name('obligaciones.create');

Route::post('/obligaciones/obtener-estado', [ObligacionesController::class, 'obtenerEstado'])->name('obtener.estado');

Route::post('/obtener-requisito-detalles', [ObligacionesController::class, 'obtenerRequisitoDetalles'])->name('obtener.requisito.detalles');

Route::post('/obtener-responsables', [ObligacionesController::class, 'obtenerResponsables'])->name('obtener.responsables');

Route::post('/filtrar-obligaciones', [ObligacionesController::class, 'filtrarObligaciones'])->name('filtrar.obligaciones');






//ruta de detalles

Route::get('/detalles', [DetallesController::class, 'index'])->name('detalles');

Route::match(['get', 'post'], '/detalles', [DetallesController::class, 'index'])->name('gestion_cumplimiento.detalles.index');

Route::post('/detalles', [DetallesController::class, 'index'])->name('filtrosDetalles');

Route::post('/export-detalles', [DetallesController::class, 'export'])->name('export-detalles');


Route::post('/filtrar-detalle', [DetallesController::class, 'filtrarDetalles'])->name('filtrar-detalle');



// En el archivo web.php
Route::get('/requisitos/{id}', [DetallesController::class, 'show'])->name('requisitos.show');




//ruta de resumen

Route::get('/resumen', [ResumenController::class, 'index'])->name('resumen');

Route::post('/api/resumen-obligaciones', [ResumenController::class, 'apiResumenObligaciones']);

Route::post('/api/obtener-avance-total', [ResumenController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');

Route::post('/api/obtener-avance-periodicidad', [ResumenController::class, 'obtenerAvancePorPeriodicidad']);







//subir archivos
Route::post('/archivos/subir', [ArchivoController::class, 'subirArchivo'])->name('archivos.subir');





// eliminar archivos
Route::post('/archivos/listar', [ArchivoController::class, 'listarArchivos'])->name('archivos.listar');

// Ruta para eliminar el archivo
Route::post('/archivos/eliminar', [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');


//recuperar archivos
Route::post('/obtener-vista-previa', [ObligacionesController::class, 'obtenerVistaPrevia'])->name('obtener.vista.previa');







//recuperar ficheros en tabla 
Route::post('/obtener-archivos', [ObligacionesController::class, 'obtenerArchivos'])->name('obtener.archivos');






Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Rutas de Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API para obtener el resumen de obligaciones
    Route::post('/api/resumen-obligaciones', [DashboardController::class, 'obtenerDatosGrafico']);

    // API para obtener el avance total
    Route::post('/api/obtener-avance-total', [DashboardController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');

    // API para obtener el avance por periodicidad
    Route::post('/api/obtener-avance-periodicidad', [DashboardController::class, 'obtenerResumenPorPeriodicidad']);

    Route::post('/filtrar-requisitos', [DashboardController::class, 'filtrarRequisitos'])->name('filtrar-requisitos');
});

