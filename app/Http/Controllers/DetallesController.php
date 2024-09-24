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

        // Redirigir al método filtrarDetalles con el año actual
        return $this->filtrarDetalles(new Request(['year' => $year]));
    }

    public function filtrarDetalles(Request $request)
    {
        // Validar la entrada del año
        $validatedData = $request->validate([
            'year' => 'required|integer|min:2024|max:2040', // Asegura que el año sea válido
        ]);

        $year = $validatedData['year'];

        // Filtrar los requisitos cuyo año de fecha_limite_cumplimiento coincida con el año seleccionado
        $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
            ->orderBy('fecha_limite_cumplimiento', 'asc')
            ->get();

        // Retornar la vista principal con los requisitos filtrados y el año seleccionado
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
