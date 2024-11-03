<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Importar Log para registro de errores

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Validación de la entrada
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (Carbon::now()->year + 20),
        ]);
    
        $year = $request->input('year', Carbon::now()->year);
        $userPuesto = Auth::user()->puesto;
    
        // Definir los puestos que verán todos los registros
        $puestosExcluidos = [
            'Director Jurídico',
            'Directora General',
            'Jefa de Cumplimiento',
            'Director de Finanzas',
            'Director de Operación',
            'Invitado'
        ];
    
        try {
            // Determinar si se aplicará el filtro de responsable
            if (in_array($userPuesto, $puestosExcluidos)) {
                // Mostrar todos los registros si el puesto está en los excluidos
                $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->orderBy('fecha_limite_cumplimiento', 'asc')
                    ->get();
            } else {
                // Aplicar filtro por puesto del usuario si no está en los excluidos
                $requisitos = Requisito::where('responsable', $userPuesto)
                    ->whereYear('fecha_limite_cumplimiento', $year)
                    ->orderBy('fecha_limite_cumplimiento', 'asc')
                    ->get();
            }
    
            // Fechas únicas de los requisitos
            $fechas = $requisitos->pluck('fecha_limite_cumplimiento')->unique()->values()->all();
    
            // Inicializar los arrays de datos para la gráfica
            $vencidasG = [];
            $porVencerG = [];
            $completasG = [];
    
            foreach ($fechas as $fecha) {
                $formattedDate = Carbon::parse($fecha)->format('Y-m-d');
    
                // Filtrar vencidas para el gráfico
                $vencidasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->where('fecha_limite_cumplimiento', '<', Carbon::now())
                    ->where('approved', '!=', 1)
                    ->count();
    
                // Filtrar por vencer para el gráfico
                $porVencerG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
                    ->where('approved', '!=', 1)
                    ->count();
    
                // Filtrar completas para el gráfico
                $completasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->where('porcentaje', 100)
                    ->count();
            }
    
            // Contar las obligaciones para el año y puesto seleccionados
            $totalObligaciones = Requisito::when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->count();
    
            // Avance total por requisito para el gráfico de avance total
            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('nombre')
                ->get()
                ->map(function ($requisito) {
                    // Asegurar que el avance total no exceda el 100%
                    $requisito->total_avance = min($requisito->total_avance, 100);
                    return $requisito;
                });

            $nombres = $resumenRequisitos->pluck('nombre');
            $avancesTotales = $resumenRequisitos->pluck('total_avance');


            

            
    
            // Calcular porcentaje de avance
// Calcular el porcentaje de avance agrupado por puesto (responsable) 
// o específico para el puesto del usuario
$avanceData = Requisito::select(
    DB::raw('COUNT(*) AS total_evidencias'),
    DB::raw('SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) AS evidencias_resueltas'),
    DB::raw('ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance_porcentaje')
)
->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
    // Filtrar solo por el puesto del usuario si no está en los puestos excluidos
    return $query->where('responsable', $userPuesto);
})
->whereYear('fecha_limite_cumplimiento', $year)
->first(); // Obtenemos solo el primer resultado (solo debería haber uno)

// Establecer valores de avance con base en el resultado de la consulta
$totalRegistros = $avanceData->total_evidencias ?? 0;
$avanceTotal = $avanceData->evidencias_resueltas ?? 0;
$porcentajeAvance = $avanceData->avance_porcentaje ?? 0;

// Retornar o usar las variables $totalRegistros, $avanceTotal y $porcentajeAvance en el código

    
            // Filtrar requisitos por estado (activas, completas, vencidas, por vencer)
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
    
        // Calcular porcentaje de requisitos completos para cada periodicidad
        $bimestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->where('periodicidad', 'bimestral')
            ->whereYear('fecha_limite_cumplimiento', $year)
            ->first();

        $semestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->where('periodicidad', 'semestral')
            ->whereYear('fecha_limite_cumplimiento', $year)
            ->first();

        $anual = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->where('periodicidad', 'anual')
            ->whereYear('fecha_limite_cumplimiento', $year)
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
