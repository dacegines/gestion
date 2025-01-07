<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ResumenController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los registros de la tabla 'Requisitos'
        $requisitos = Requisito::orderBy('fecha_limite_cumplimiento', 'asc')->get();

        // Fechas únicas de los requisitos
        $fechas = Requisito::orderBy('fecha_limite_cumplimiento', 'asc')
            ->pluck('fecha_limite_cumplimiento')
            ->unique()
            ->values()
            ->all();

        // Inicializar los arrays de datos para la gráfica
        $vencidasG = [];
        $porVencerG = [];
        $completasG = [];

        foreach ($fechas as $fecha) {
            // Contar las vencidas por fecha y excluye las que están aprobadas
            $vencidasG[] = Requisito::whereDate('fecha_limite_cumplimiento', $fecha)
                ->where('fecha_limite_cumplimiento', '<', Carbon::now())
                ->where('approved', '!=', 1) // Filtrar las que no están aprobadas
                ->count();

            // Contar las por vencer por fecha y excluye las que están aprobadas
            $porVencerG[] = Requisito::whereDate('fecha_limite_cumplimiento', $fecha)
                ->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
                ->where('approved', '!=', 1) // Filtrar las que no están aprobadas
                ->count();

            // Contar las completas por fecha
            $completasG[] = Requisito::whereDate('fecha_limite_cumplimiento', $fecha)
                ->where('porcentaje', 100)
                ->count();
        }

        // Contar las evidencias sin repetir
        $totalObligaciones = Requisito::count('evidencia');

        // Contar las activas: fecha_limite_cumplimiento > 30 días a partir de hoy y no aprobadas
        $requisitosActivos = Requisito::where('fecha_limite_cumplimiento', '>', Carbon::now()->addDays(30))
            ->where('approved', '!=', 1)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();
        $activas = Requisito::where('fecha_limite_cumplimiento', '>', Carbon::now()->addDays(30))
            ->where('approved', '!=', 1)
            ->count();

        // Contar las completas: porcentaje = 100
        $requisitosCompletos = Requisito::where('porcentaje', 100)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();
        $completas = Requisito::where('porcentaje', '=', 100)->count();

        // Contar las vencidas: fecha actual > fecha_limite_cumplimiento y no aprobadas
        $requisitosVencidos = Requisito::where('fecha_limite_cumplimiento', '<', Carbon::now())
            ->where('approved', '!=', 1)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();
        $vencidas = Requisito::where('fecha_limite_cumplimiento', '<', Carbon::now())
            ->where('approved', '!=', 1)
            ->count();

        // Contar las por vencer: fecha_limite_cumplimiento entre hoy y 30 días desde hoy y no aprobadas
        $requisitosPorVencer = Requisito::whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
            ->where('approved', '!=', 1)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();
        $porVencer = Requisito::whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
            ->where('approved', '!=', 1)
            ->count();

        // Pasar los registros a la vista
        return view('gestion_cumplimiento.resumen.index', compact(
            'totalObligaciones',
            'activas',
            'completas',
            'vencidas',
            'porVencer',
            'requisitos',
            'requisitosCompletos',
            'requisitosActivos',
            'requisitosVencidos',
            'requisitosPorVencer',
            'fechas',
            'vencidasG',
            'porVencerG',
            'completasG'
        ));
    }


    public function apiResumenObligaciones(Request $request)
    {
        // Obtener los nombres y el total de avance agrupados por 'nombre'
        $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
            ->groupBy('nombre')  // Agrupar por 'nombre' únicamente
            ->get();

        // Retornar los datos en formato JSON
        return response()->json($resumenRequisitos);
    }


    public function obtenerAvanceTotal()
    {
        // Contar el total de registros únicos en la columna 'nombre'
        $totalRegistros = Requisito::distinct('nombre')->count('nombre');

        // Calcular la suma del avance total
        $avanceTotal = Requisito::sum('avance');

        // Calcular el porcentaje de avance
        $porcentajeAvance = ($totalRegistros > 0) ? round(($avanceTotal / ($totalRegistros * 100)) * 100, 2) : 0;

        return response()->json([
            'total' => 100,  // Total es siempre 100%
            'avance' => $porcentajeAvance
        ]);
    }


    public function obtenerAvancePorPeriodicidad()
    {
        // Consulta para obtener la suma de avance para 'bimestral'
        $bimestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
            ->where('periodicidad', 'bimestral')
            ->groupBy('periodicidad')
            ->first();

        // Consulta para obtener la suma de avance para 'semestral'
        $semestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
            ->where('periodicidad', 'semestral')
            ->groupBy('periodicidad')
            ->first();

        // Consulta para obtener la suma de avance para 'anual'
        $anual = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
            ->where('periodicidad', 'anual')
            ->groupBy('periodicidad')
            ->first();

        return response()->json([
            'bimestral' => $bimestral,
            'semestral' => $semestral,
            'anual' => $anual
        ]);
    }
}
