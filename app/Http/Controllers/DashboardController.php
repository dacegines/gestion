<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Importar Log para registro de errores
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        // Obtener el ID del usuario autenticado
        $user_id = Auth::id();

        // Validación de la entrada
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (Carbon::now()->year + 20),
        ]);

        // Capturar el año, con un valor predeterminado si no se proporciona
        $year = $request->input('year', Carbon::now()->year);
        $userPuesto = Auth::user()->puesto;

        $status = $request->input('status', 'default_status');

        // Definir los puestos que verán todos los registros
        $puestosExcluidos = DB::table('users')
            ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
            ->where('model_has_authorizations.authorization_id', 7) // Cambia el ID si necesitas otra autorización
            ->distinct()
            ->pluck('users.puesto')
            ->toArray();

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
            $resumenRequisitos = Requisito::select(
                'nombre',
                DB::raw("LEAST(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 100) AS total_avance")
            )
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('nombre')
                ->orderBy('total_avance', 'DESC')
                ->get();

            // Extraer nombres y porcentaje de avance para la gráfica
            $nombres = $resumenRequisitos->pluck('nombre');
            $avancesTotales = $resumenRequisitos->pluck('total_avance')->map(function ($avance) {
                return (float) number_format($avance, 2); // Convertir a número y redondear a 2 decimales
            });

            // Calcular porcentaje de avance agrupado por puesto (responsable) 
            $avanceData = Requisito::select(
                DB::raw('COUNT(*) AS total_evidencias'),
                DB::raw('SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) AS evidencias_resueltas'),
                DB::raw('ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance_porcentaje')
            )
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->first();

            // Establecer valores de avance con base en el resultado de la consulta
            $totalRegistros = $avanceData->total_evidencias ?? 0;
            $avanceTotal = $avanceData->evidencias_resueltas ?? 0;
            $porcentajeAvance = $avanceData->avance_porcentaje ?? 0;

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

            $mostrarBimestral = !is_null($bimestral) && $bimestral->avance > 0;
            $mostrarSemestral = !is_null($semestral) && $semestral->avance > 0;
            $mostrarAnual = !is_null($anual) && $anual->avance > 0;

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
                'year',
                'mostrarBimestral',
                'mostrarSemestral',
                'mostrarAnual',
                'user_id',
                'status'
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

    public function descargarPDF(Request $request)
    {
        // Captura los datos necesarios
        $year = $request->input('year', date('Y'));
        $user_id = $request->input('user_id');
        $status = $request->input('status');
        $chartImageAvanceObligaciones = $request->input('chartImageAvanceObligaciones');
        $chartImageAvanceTotal = $request->input('chartImageAvanceTotal');
        $chartImageEstatusGeneral = $request->input('chartImageEstatusGeneral');

        // Obtener el puesto del usuario autenticado
        $userPuesto = Auth::user()->puesto;

        // Definir los puestos que verán todos los registros
        $puestosExcluidos = [
            'Gerente Juri­dico',
            'Directora General',
            'Jefa de Cumplimiento',
            'Director de Finanzas',
            'Director de Operación, Mtto y TI',
            'Invitado'
        ];

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

        // Calcular los totales de la misma manera que en el método index
        $totalObligaciones = $requisitos->count();
        $activas = $requisitos->where('fecha_limite_cumplimiento', '>', Carbon::now()->addDays(30))
            ->where('approved', '!=', 1)
            ->count();
        $completas = $requisitos->where('porcentaje', 100)->count();
        $vencidas = $requisitos->where('fecha_limite_cumplimiento', '<', Carbon::now())
            ->where('approved', '!=', 1)
            ->count();
        $porVencer = $requisitos->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
            ->where('approved', '!=', 1)
            ->count();

        // Calcular periodicidad de avance
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

        // Determinar si mostrar cada periodicidad en el PDF
        $mostrarBimestral = !is_null($bimestral) && $bimestral->avance > 0;
        $mostrarSemestral = !is_null($semestral) && $semestral->avance > 0;
        $mostrarAnual = !is_null($anual) && $anual->avance > 0;

        // Preparar los datos para la vista del PDF
        $data = [
            'year' => $year,
            'user_id' => $user_id,
            'status' => $status,
            'totalObligaciones' => $totalObligaciones,
            'activas' => $activas,
            'completas' => $completas,
            'vencidas' => $vencidas,
            'porVencer' => $porVencer,
            'chartImageAvanceObligaciones' => $chartImageAvanceObligaciones,
            'chartImageAvanceTotal' => $chartImageAvanceTotal,
            'chartImageEstatusGeneral' => $chartImageEstatusGeneral,
            'bimestral' => $bimestral,
            'semestral' => $semestral,
            'anual' => $anual,
            'mostrarBimestral' => $mostrarBimestral,
            'mostrarSemestral' => $mostrarSemestral,
            'mostrarAnual' => $mostrarAnual,
            'userPuesto' => $userPuesto
        ];

        // Generar el PDF usando la vista
        $pdf = Pdf::loadView('pdf.resumen_pdf', $data);
        return $pdf->download('reporte_resumen.pdf');
    }
}
