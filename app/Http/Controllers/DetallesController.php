<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use Illuminate\Http\Request;
use App\Exports\RequisitosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth; // Importante para manejar la autenticación

class DetallesController extends Controller
{
    
    public function index(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login'); // Redirigir al login si no está autenticado
        }
    
        $hoy = \Carbon\Carbon::now();
        $year = $hoy->year; // Establece el año actual
        $user = Auth::user(); // Obtener el usuario autenticado
    
        // Definir los puestos excluidos
        $puestosExcluidos = [
            'Director Jurídico',
            'Directora General',
            'Jefa de Cumplimiento',
            'Director de Finanzas',
            'Director de Operación',
            'Invitado'
        ];
    
        // Filtrar los requisitos dependiendo del puesto del usuario
        if (in_array($user->puesto, $puestosExcluidos)) {
            // Si el usuario tiene un puesto en la lista de excluidos, mostrar todos los registros
            $requisitos = Requisito::with('archivos')
                ->whereYear('fecha_limite_cumplimiento', $year) // Mostrar todas las obligaciones para el año actual
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();
        } else {
            // Si el usuario no está en la lista de excluidos, filtrar solo por su puesto
            $requisitos = Requisito::with('archivos')
                ->where('responsable', $user->puesto) // Filtrar por el puesto del usuario
                ->whereYear('fecha_limite_cumplimiento', $year)
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();
        }
    
        return view('gestion_cumplimiento.detalles.index', compact('requisitos', 'year'));
    }
    
    

    public function filtrarDetalles(Request $request)
{
    $validatedData = $request->validate([
        'year' => 'required|integer|min:2024|max:2040',
    ]);

    $year = $validatedData['year'];

    // Asegurarse de cargar los archivos relacionados
    $requisitos = Requisito::with('archivos')
        ->whereYear('fecha_limite_cumplimiento', $year)
        ->orderBy('fecha_limite_cumplimiento', 'asc')
        ->get();

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
}
