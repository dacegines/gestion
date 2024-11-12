<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use Illuminate\Http\Request;
use App\Exports\RequisitosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth; // Importante para manejar la autenticación
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DetallesController extends Controller
{
    
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    
        $hoy = \Carbon\Carbon::now();
        $year = $hoy->year;
        $user = Auth::user();
    
        $puestosExcluidos = [
            'Director Jurídico', 'Directora General', 'Jefa de Cumplimiento', 
            'Director de Finanzas', 'Director de Operación', 'Invitado'
        ];
    
        // Consulta principal para obtener los requisitos
        $requisitosQuery = DB::table('requisitos as r')
            ->select(
                'r.id', 'r.numero_evidencia', 
                'r.clausula_condicionante_articulo as clausula',
                'r.evidencia as requisito_evidencia', 'r.periodicidad', 
                'r.fecha_limite_cumplimiento', 'r.responsable', 
                'r.porcentaje',
                DB::raw("CASE 
                    WHEN r.porcentaje = 100 THEN 'Cumplido'
                    WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                    WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo a Vencer'
                    ELSE 'Activo'
                END AS estatus")
            )
            ->whereYear('r.fecha_limite_cumplimiento', $year)
            ->orderBy('r.fecha_limite_cumplimiento', 'asc');
    
        if (!in_array($user->puesto, $puestosExcluidos)) {
            $requisitosQuery->where('r.responsable', $user->puesto);
        }
    
        $requisitos = $requisitosQuery->get();
    
        // Consulta independiente para contar archivos por fecha_limite_cumplimiento
        $conteoArchivos = DB::table('archivos')
            ->select('fecha_limite_cumplimiento', DB::raw('COUNT(*) as cantidad_archivos'))
            ->groupBy('fecha_limite_cumplimiento')
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get()
            ->keyBy('fecha_limite_cumplimiento');
    
        // Agregar el conteo de archivos a cada requisito
        foreach ($requisitos as $requisito) {
            $fechaLimite = $requisito->fecha_limite_cumplimiento;
            $requisito->cantidad_archivos = $conteoArchivos->has($fechaLimite) ? $conteoArchivos->get($fechaLimite)->cantidad_archivos : 0;
        }
    
        return view('gestion_cumplimiento.detalles.index', compact('requisitos', 'year'));
    }
    
    
    
    
    

    public function filtrarDetalles(Request $request)
    {
        $validatedData = $request->validate([
            'year' => 'required|integer|min:2024|max:2040',
        ]);
    
        $year = $validatedData['year'];
        $user = Auth::user(); // Obtener el usuario autenticado
    
        // Definir los puestos excluidos
        $puestosExcluidos = [
            'Director Jurídico', 'Directora General', 'Jefa de Cumplimiento', 
            'Director de Finanzas', 'Director de Operación', 'Invitado'
        ];
    
        // Construir la consulta de requisitos
        $requisitosQuery = DB::table('requisitos as r')
            ->select(
                'r.id', 
                'r.numero_evidencia', 
                'r.clausula_condicionante_articulo as clausula',
                'r.evidencia as requisito_evidencia', 
                'r.periodicidad', 
                'r.fecha_limite_cumplimiento', 
                'r.responsable', 
                'r.porcentaje',
                DB::raw("CASE 
                    WHEN r.porcentaje = 100 THEN 'Cumplido'
                    WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                    WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo a Vencer'
                    ELSE 'Activo'
                END AS estatus")
            )
            ->whereYear('r.fecha_limite_cumplimiento', $year)
            ->orderBy('r.fecha_limite_cumplimiento', 'asc');
    
        // Aplicar el filtro de puesto si el usuario no está en los puestos excluidos
        if (!in_array($user->puesto, $puestosExcluidos)) {
            $requisitosQuery->where('r.responsable', $user->puesto);
        }
    
        // Ejecutar la consulta
        $requisitos = $requisitosQuery->get();
    
        // Obtener el conteo de archivos para cada fecha límite de cumplimiento
        $conteoArchivos = DB::table('archivos')
            ->select('fecha_limite_cumplimiento', DB::raw('COUNT(*) as cantidad_archivos'))
            ->groupBy('fecha_limite_cumplimiento')
            ->get()
            ->keyBy('fecha_limite_cumplimiento');
    
        // Agregar el conteo de archivos a cada requisito
        foreach ($requisitos as $requisito) {
            $fechaLimite = $requisito->fecha_limite_cumplimiento;
            $requisito->cantidad_archivos = $conteoArchivos->has($fechaLimite) ? $conteoArchivos->get($fechaLimite)->cantidad_archivos : 0;
        }
    
        return view('gestion_cumplimiento.detalles.index', compact('requisitos', 'year'));
    }
    
    

    public function export(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login'); // Redirigir al login si no está autenticado
        }

        // Validar la entrada del año
        $validatedData = $request->validate([
            'year' => 'required|integer|min:2024|max:2040', // Validar año entre 2024 y 2040
        ]);

        $year = $validatedData['year'];

        // Filtrar los requisitos por el año seleccionado
        $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();

        // Pasar los requisitos filtrados al exportador
        return Excel::download(new RequisitosExport($requisitos), 'requisitos.xlsx');
    }
    public function obtenerArchivosPorFecha($fecha_limite_cumplimiento)
    {
        $archivos = DB::table('archivos')
            ->where('fecha_limite_cumplimiento', $fecha_limite_cumplimiento)
            ->select('nombre_archivo', 'ruta_archivo')
            ->get();
    
        return response()->json($archivos);
    }
    
    public function descargarPDF(Request $request)
    {
        $year = $request->input('year', \Carbon\Carbon::now()->year);
        $search = $request->input('search');
    
        // Construir la consulta principal
        $requisitosQuery = DB::table('requisitos as r')
            ->select(
                'r.numero_evidencia',
                'r.clausula_condicionante_articulo as clausula',
                'r.evidencia as requisito_evidencia',
                'r.periodicidad',
                'r.fecha_limite_cumplimiento',
                'r.responsable',
                'r.porcentaje',
                DB::raw("CASE 
                    WHEN r.porcentaje = 100 THEN 'Cumplido'
                    WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                    WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo a Vencer'
                    ELSE 'Activo'
                END AS estatus"),
                DB::raw("(SELECT COUNT(*) FROM archivos a WHERE a.fecha_limite_cumplimiento = r.fecha_limite_cumplimiento) as cantidad_archivos")
            )
            ->whereYear('r.fecha_limite_cumplimiento', $year);
    
        // Aplicar el filtro de búsqueda si se proporciona
        if (!empty($search)) {
            $requisitosQuery->where(function ($query) use ($search) {
                $query->where('r.numero_evidencia', 'like', "%$search%")
                      ->orWhere('r.clausula_condicionante_articulo', 'like', "%$search%")
                      ->orWhere('r.evidencia', 'like', "%$search%")
                      ->orWhere('r.periodicidad', 'like', "%$search%")
                      ->orWhere('r.responsable', 'like', "%$search%")
                      ->orWhere(DB::raw("CASE 
                            WHEN r.porcentaje = 100 THEN 'Cumplido'
                            WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                            WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo a Vencer'
                            ELSE 'Activo'
                        END"), 'like', "%$search%");
            });
        }
    
        $requisitos = $requisitosQuery->get();
    
        // Verificar si hay resultados antes de generar el PDF
        if ($requisitos->isEmpty() && $search) {
            return redirect()->back()->with('error', 'No se encontraron registros para el filtro aplicado.');
        }
    
        // Generar el PDF
        $pdf = Pdf::loadView('pdf.reporte', compact('requisitos', 'year'))
                  ->setPaper('A4', 'landscape');
    
        return $pdf->download('reporte_detalles.pdf');
    }
    
    
    
    
    
    

}
