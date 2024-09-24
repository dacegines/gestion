<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Importar Log para registro de errores

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Validación de la entrada
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:'.(Carbon::now()->year + 20),
        ]);

        $year = $request->input('year', Carbon::now()->year);

        try {
            // Obtener los registros filtrados por año
            $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();

            // Fechas únicas de los requisitos
            $fechas = $requisitos->pluck('fecha_limite_cumplimiento')->unique()->values()->all();

            // Inicializar los arrays de datos para la gráfica
            $vencidasG = [];
            $porVencerG = [];
            $completasG = [];

            foreach ($fechas as $fecha) {
                $fecha = Carbon::parse($fecha)->format('Y-m-d');

                $vencidasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->whereDate('fecha_limite_cumplimiento', $fecha)
                    ->where('fecha_limite_cumplimiento', '<', Carbon::now())
                    ->where('approved', '!=', 1)
                    ->count();

                $porVencerG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->whereDate('fecha_limite_cumplimiento', $fecha)
                    ->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
                    ->where('approved', '!=', 1)
                    ->count();

                $completasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->whereDate('fecha_limite_cumplimiento', $fecha)
                    ->where('porcentaje', 100)
                    ->count();
            }

            // Contar las obligaciones para el año seleccionado
            $totalObligaciones = Requisito::whereYear('fecha_limite_cumplimiento', $year)->count('evidencia');

            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('nombre')
                ->get();

            $nombres = $resumenRequisitos->pluck('nombre');
            $avancesTotales = $resumenRequisitos->pluck('total_avance');

            $totalRegistros = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                ->distinct('nombre')
                ->count('nombre');
            $avanceTotal = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                ->sum('avance');
            $porcentajeAvance = ($totalRegistros > 0) ? round(($avanceTotal / ($totalRegistros * 100)) * 100, 2) : 0;

            $requisitosActivos = $requisitos->where('fecha_limite_cumplimiento', '>', Carbon::now()->addDays(30))
                ->where('approved', '!=', 1);
            $activas = $requisitosActivos->count();

            $requisitosCompletos = $requisitos->where('porcentaje', 100);
            $completas = $requisitosCompletos->count();

            $requisitosVencidos = $requisitos->where('fecha_limite_cumplimiento', '<', Carbon::now())
                ->where('approved', '!=', 1);
            $vencidas = $requisitosVencidos->count();

            $requisitosPorVencer = $requisitos->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
                ->where('approved', '!=', 1);
            $porVencer = $requisitosPorVencer->count();

            // Resumen por periodicidad
            $bimestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
                ->where('periodicidad', 'bimestral')
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('periodicidad')
                ->first();

            $semestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
                ->where('periodicidad', 'semestral')
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('periodicidad')
                ->first();

            $anual = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
                ->where('periodicidad', 'anual')
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('periodicidad')
                ->first();

            return view('dashboard', compact(
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
                'completasG',
                'nombres',
                'avancesTotales',
                'porcentajeAvance',
                'bimestral',
                'semestral',
                'anual',
                'year'
            ));
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@index: ' . $e->getMessage());
            return redirect()->back()->withErrors('Ocurrió un error al cargar el dashboard.');
        }
    }

    public function apiResumenObligaciones(Request $request)
    {
        try {
            // Obtener los nombres y el total de avance agrupados por 'nombre'
            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->groupBy('nombre')  // Agrupar por 'nombre' únicamente
                ->get();

            // Retornar los datos en formato JSON
            return response()->json($resumenRequisitos);
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@apiResumenObligaciones: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los datos.'], 500);
        }
    }

    public function obtenerDatosGrafico()
    {
        try {
            // Obtener los datos para los gráficos del dashboard
            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->groupBy('nombre')  // Agrupar por 'nombre' únicamente
                ->get();

            // Retornar los datos en formato JSON
            return response()->json($resumenRequisitos);
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@obtenerDatosGrafico: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los datos del gráfico.'], 500);
        }
    }

    public function obtenerAvanceTotal()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@obtenerAvanceTotal: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener el avance total.'], 500);
        }
    }

    public function obtenerResumenPorPeriodicidad()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@obtenerResumenPorPeriodicidad: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener el resumen por periodicidad.'], 500);
        }
    }

    public function filtrarRequisitos(Request $request)
    {
        return $this->index($request);
    }
}
