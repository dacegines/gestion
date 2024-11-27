<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ObligacionesController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\DetallesController;
use App\Http\Controllers\ResumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomPasswordResetController;
use App\Http\Controllers\CustomRegisterController;
use App\Http\Controllers\UsuarioController;

// Ruta de inicio
Route::get('/', function () {
    return view('auth.login');
});

// Grupo de middleware para autenticación, sesión y verificación de correo
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/profile', [UsuarioController::class, 'profile']);

    // Rutas de DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/api/resumen-obligaciones', [DashboardController::class, 'obtenerDatosGrafico']);
    Route::post('/api/obtener-avance-total', [DashboardController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');
    Route::post('/api/obtener-avance-periodicidad', [DashboardController::class, 'obtenerResumenPorPeriodicidad']);
    Route::post('/filtrar-requisitos', [DashboardController::class, 'filtrarRequisitos'])->name('filtrar-requisitos');
    Route::post('/descargar-pdf', [DashboardController::class, 'descargarPDF'])->name('descargar-pdf');

    // Rutas de ObligacionesController
    Route::get('/obligaciones', [ObligacionesController::class, 'index'])->name('obligaciones');
    Route::get('/AddObligaciones', [ObligacionesController::class, 'store'])->name('obligaciones.create');
    Route::post('/obtener-detalles', [ObligacionesController::class, 'getDetallesEvidencia'])->name('obtener.detalles');
    Route::post('/obtener-notificaciones', [ObligacionesController::class, 'obtenerNotificaciones'])->name('obtener.notificaciones');
    Route::post('/obtener-tabla-notificaciones', [ObligacionesController::class, 'obtenerTablaNotificaciones'])->name('obtener.tabla.notificaciones');
    Route::post('/requisito/cambiar-estado', [ObligacionesController::class, 'cambiarEstado'])->name('requisito.cambiarEstado');
    Route::post('/obtener-detalle-evidencia', [ObligacionesController::class, 'obtenerDetalleEvidencia'])->name('obtener.detalle.evidencia');
    Route::post('/ruta-enviar-correo-datos-evidencia', [ObligacionesController::class, 'enviarCorreoDatosEvidencia'])->name('enviar.correo.datos.evidencia');
    Route::post('/obligaciones/verificar-archivos', [ObligacionesController::class, 'verificarArchivos'])->name('obligaciones.verificarArchivos');
    Route::post('/actualizar-porcentaje', [ObligacionesController::class, 'actualizarPorcentaje'])->name('actualizar.porcentaje');
    Route::post('/actualizar-suma-porsentaje', [ObligacionesController::class, 'actualizarPorcentajeSuma'])->name('actualizar.suma.porcentaje');
    Route::post('/obligaciones/obtener-estado', [ObligacionesController::class, 'obtenerEstado'])->name('obtener.estado');
    Route::post('/obtener-requisito-detalles', [ObligacionesController::class, 'obtenerRequisitoDetalles'])->name('obtener.requisito.detalles');
    Route::post('/obtener-responsables', [ObligacionesController::class, 'obtenerResponsables'])->name('obtener.responsables');
    Route::post('/filtrar-obligaciones', [ObligacionesController::class, 'filtrarObligaciones'])->name('filtrar.obligaciones');
    Route::post('/approved-result', [ObligacionesController::class, 'obtenerEstadoApproved'])->name('approved.resul');
    Route::post('/enviar-correo-alerta', [ObligacionesController::class, 'enviarCorreoAlerta']);

    // Rutas de DetallesController
    Route::get('/detalles', [DetallesController::class, 'index'])->name('detalles');
    Route::match(['get', 'post'], '/detalles', [DetallesController::class, 'index'])->name('gestion_cumplimiento.detalles.index');
    Route::post('/detalles', [DetallesController::class, 'index'])->name('filtrosDetalles');
    Route::post('/export-detalles', [DetallesController::class, 'export'])->name('export-detalles');
    Route::post('/filtrar-detalle', [DetallesController::class, 'filtrarDetalles'])->name('filtrar-detalle');
    Route::get('/requisitos/{id}', [DetallesController::class, 'show'])->name('requisitos.show');
    Route::get('/obtener-archivos/{fecha_limite_cumplimiento}', [DetallesController::class, 'obtenerArchivosPorFecha'])->name('obtener.archivos.fecha');
    Route::get('/descargar-pdf', [DetallesController::class, 'descargarPDF'])->name('descargar.pdf');

    // Rutas de ResumenController
    Route::get('/resumen', [ResumenController::class, 'index'])->name('resumen');
    Route::post('/api/resumen-obligaciones', [ResumenController::class, 'apiResumenObligaciones']);
    Route::post('/api/obtener-avance-total', [ResumenController::class, 'obtenerAvanceTotal'])->name('api.obtenerAvanceTotal');
    Route::post('/api/obtener-avance-periodicidad', [ResumenController::class, 'obtenerAvancePorPeriodicidad']);

    // Rutas de ArchivoController
    Route::post('/archivos/subir', [ArchivoController::class, 'subirArchivo'])->name('archivos.subir');
    Route::post('/archivos/listar', [ArchivoController::class, 'listarArchivos'])->name('archivos.listar');
    Route::post('/archivos/eliminar', [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');
});

// Rutas de CustomPasswordResetController
Route::get('custom-password-reset', [CustomPasswordResetController::class, 'show'])->name('custom.password.reset');
Route::post('custom-password-reset', [CustomPasswordResetController::class, 'submitRequest'])->name('custom.password.reset.submit');

// Rutas de CustomRegisterController
Route::get('/register_new', [CustomRegisterController::class, 'show'])->name('custom.register_new');
Route::post('/register_new', [CustomRegisterController::class, 'submitRequest'])->name('custom.account.register.submit');
