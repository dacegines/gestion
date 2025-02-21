<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use App\Models\ObligacionUsuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    
        $user = Auth::user();
        $user_id = $user->id;
    
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (Carbon::now()->year + 20),
        ]);
    
        $year = $request->input('year', Carbon::now()->year);
        $userPuesto = $user->puesto;
        $status = $request->input('status', 'default_status');
    
        $puestosExcluidos = DB::table('users')
            ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
            ->where('model_has_authorizations.authorization_id', 7)
            ->distinct()
            ->pluck('users.puesto')
            ->toArray();
    
        try {
            $requisitosIds = ObligacionUsuario::where('user_id', $user_id)
                ->where('view', 1)
                ->pluck('numero_evidencia')
                ->toArray();
    
            if (in_array($userPuesto, $puestosExcluidos)) {
                $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                        return $query->whereIn('numero_evidencia', $requisitosIds);
                    })
                    ->orderBy('fecha_limite_cumplimiento', 'asc')
                    ->get();
            } else {
                $requisitos = Requisito::where('responsable', $userPuesto)
                    ->whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                        return $query->whereIn('numero_evidencia', $requisitosIds);
                    })
                    ->orderBy('fecha_limite_cumplimiento', 'asc')
                    ->get();
            }
    
            $fechas = $requisitos->pluck('fecha_limite_cumplimiento')->unique()->values()->all();
    
            $vencidasG = [];
            $porVencerG = [];
            $completasG = [];
    
            foreach ($fechas as $fecha) {
                $formattedDate = Carbon::parse($fecha)->format('Y-m-d');
    
                $vencidasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                        return $query->whereIn('numero_evidencia', $requisitosIds);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->where('fecha_limite_cumplimiento', '<', Carbon::now())
                    ->where('approved', '!=', 1)
                    ->count();
    
                $porVencerG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                        return $query->whereIn('numero_evidencia', $requisitosIds);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->whereBetween('fecha_limite_cumplimiento', [Carbon::now(), Carbon::now()->addDays(30)])
                    ->where('approved', '!=', 1)
                    ->count();
    
                $completasG[] = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                    ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                        return $query->where('responsable', $userPuesto);
                    })
                    ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                        return $query->whereIn('numero_evidencia', $requisitosIds);
                    })
                    ->whereDate('fecha_limite_cumplimiento', $formattedDate)
                    ->where('porcentaje', 100)
                    ->count();
            }
    
            // Agrupar fechas y datos por mes
            $fechasAgrupadas = [];
            $vencidasAgrupadas = [];
            $porVencerAgrupadas = [];
            $completasAgrupadas = [];
            $porcentajesEficiencia = []; // Nuevo array para almacenar los porcentajes de eficiencia
    
            foreach ($fechas as $index => $fecha) {
                $mes = strtoupper(Carbon::parse($fecha)->locale('es')->isoFormat('MMMM')); // Formato: Nombre del mes en español
    
                if (!isset($fechasAgrupadas[$mes])) {
                    $fechasAgrupadas[$mes] = $mes;
                    $vencidasAgrupadas[$mes] = 0;
                    $porVencerAgrupadas[$mes] = 0;
                    $completasAgrupadas[$mes] = 0;
                }
    
                // Sumar los valores por mes
                $vencidasAgrupadas[$mes] += $vencidasG[$index];
                $porVencerAgrupadas[$mes] += $porVencerG[$index];
                $completasAgrupadas[$mes] += $completasG[$index];
    
                // Calcular el porcentaje de eficiencia para cada mes
                $totalObligaciones = $vencidasAgrupadas[$mes] + $porVencerAgrupadas[$mes] + $completasAgrupadas[$mes];
                $porcentajesEficiencia[$mes] = ($totalObligaciones > 0) 
                    ? round(($completasAgrupadas[$mes] / $totalObligaciones) * 100, 2) 
                    : 0;
            }
    
            // Convertir los arrays asociativos a arrays indexados
            $fechasAgrupadas = array_values($fechasAgrupadas);
            $vencidasAgrupadas = array_values($vencidasAgrupadas);
            $porVencerAgrupadas = array_values($porVencerAgrupadas);
            $completasAgrupadas = array_values($completasAgrupadas);
            $porcentajesEficiencia = array_values($porcentajesEficiencia); // Convertir a array indexado
    
            $totalObligaciones = Requisito::when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->count();
    
            $resumenRequisitos = Requisito::select(
                'nombre',
                DB::raw("LEAST(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 100) AS total_avance")
            )
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->groupBy('nombre')
                ->orderBy('total_avance', 'DESC')
                ->get();
    
            $nombres = $resumenRequisitos->pluck('nombre');
            $avancesTotales = $resumenRequisitos->pluck('total_avance')->map(function ($avance) {
                return (float) number_format($avance, 2);
            });
    
            $avanceData = Requisito::select(
                DB::raw('COUNT(*) AS total_evidencias'),
                DB::raw('SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) AS evidencias_resueltas'),
                DB::raw('ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance_porcentaje')
            )
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->first();
    
            $totalRegistros = $avanceData->total_evidencias ?? 0;
            $avanceTotal = $avanceData->evidencias_resueltas ?? 0;
            $porcentajeAvance = $avanceData->avance_porcentaje ?? 0;
    
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
    
            $bimestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->where('periodicidad', 'bimestral')
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->first();
    
            $semestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->where('periodicidad', 'semestral')
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->first();
    
            $anual = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
                ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                    return $query->where('responsable', $userPuesto);
                })
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
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
                'fechasAgrupadas', 
                'vencidasAgrupadas', 
                'porVencerAgrupadas',
                'completasAgrupadas',
                'porcentajesEficiencia', // Nuevo array de porcentajes de eficiencia
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

            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->groupBy('nombre')
                ->get();


            return response()->json($resumenRequisitos);
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@apiResumenObligaciones: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los datos.'], 500);
        }
    }

    public function obtenerDatosGrafico()
    {
        try {

            $resumenRequisitos = Requisito::select('nombre', DB::raw('SUM(avance) as total_avance'))
                ->groupBy('nombre')
                ->get();


            return response()->json($resumenRequisitos);
        } catch (\Exception $e) {
            Log::error('Error en DashboardController@obtenerDatosGrafico: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los datos del gráfico.'], 500);
        }
    }

    public function obtenerAvanceTotal()
    {
        try {

            $totalRegistros = Requisito::distinct('nombre')->count('nombre');


            $avanceTotal = Requisito::sum('avance');


            $porcentajeAvance = ($totalRegistros > 0) ? round(($avanceTotal / ($totalRegistros * 100)) * 100, 2) : 0;

            return response()->json([
                'total' => 100,
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

            $bimestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
                ->where('periodicidad', 'bimestral')
                ->groupBy('periodicidad')
                ->first();


            $semestral = Requisito::select('periodicidad', DB::raw('SUM(avance) as avance'))
                ->where('periodicidad', 'semestral')
                ->groupBy('periodicidad')
                ->first();


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

        $year = $request->input('year', date('Y'));
        $user_id = $request->input('user_id');
        $status = $request->input('status');
        $chartImageAvanceObligaciones = $request->input('chartImageAvanceObligaciones');
        $chartImageAvanceTotal = $request->input('chartImageAvanceTotal');
        $chartImageEstatusGeneral = $request->input('chartImageEstatusGeneral');


        $user = Auth::user();
        $userPuesto = $user->puesto;


        $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->pluck('numero_evidencia')
            ->toArray();



        $puestosExcluidos = DB::table('users')
            ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
            ->where('model_has_authorizations.authorization_id', 7)
            ->distinct()
            ->pluck('users.puesto')
            ->toArray();


        if (in_array($userPuesto, $puestosExcluidos)) {

            $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();
        } else {

            $requisitos = Requisito::where('responsable', $userPuesto)
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                    return $query->whereIn('numero_evidencia', $requisitosIds);
                })
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();
        }


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


        $bimestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                return $query->whereIn('numero_evidencia', $requisitosIds);
            })
            ->where('periodicidad', 'bimestral')
            ->whereYear('fecha_limite_cumplimiento', $year)
            ->first();

        $semestral = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                return $query->whereIn('numero_evidencia', $requisitosIds);
            })
            ->where('periodicidad', 'semestral')
            ->whereYear('fecha_limite_cumplimiento', $year)
            ->first();

        $anual = Requisito::select(DB::raw("ROUND(SUM(CASE WHEN porcentaje = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS avance"))
            ->when(!in_array($userPuesto, $puestosExcluidos), function ($query) use ($userPuesto) {
                return $query->where('responsable', $userPuesto);
            })
            ->when(!empty($requisitosIds), function ($query) use ($requisitosIds) {
                return $query->whereIn('numero_evidencia', $requisitosIds);
            })
            ->where('periodicidad', 'anual')
            ->whereYear('fecha_limite_cumplimiento', $year)
            ->first();


        $mostrarBimestral = !is_null($bimestral) && $bimestral->avance > 0;
        $mostrarSemestral = !is_null($semestral) && $semestral->avance > 0;
        $mostrarAnual = !is_null($anual) && $anual->avance > 0;


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


        $pdf = Pdf::loadView('pdf.resumen_pdf', $data);
        return $pdf->download('reporte_resumen.pdf');
    }
}
