<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use Illuminate\Http\Request;

use App\Exports\RequisitosExport;
use Maatwebsite\Excel\Facades\Excel;

class DetallesController extends Controller
{
public function index()
{
    $query = Requisito::query();

    $requisitos = $query->get();



    return view('gestion_cumplimiento.detalles.index', compact('requisitos'));
}

    public function detalleFiltro(Request $request) {

        $query = Requisito::query();

        if ($request->filled('startDate')) {
            $query->where('fecha_limite_cumplimiento', '>=', $request->startDate);
        }
    
        if ($request->filled('endDate')) {
            $query->where('fecha_limite_cumplimiento', '<=', $request->endDate);
        }
    
        if ($request->filled('sortOrder')) {
            switch ($request->sortOrder) {
                case 'numReqAsc':
                    $query->orderBy('numero_requisito', 'asc');
                    break;
                case 'numReqDesc':
                    $query->orderBy('numero_requisito', 'desc');
                    break;
                case 'fechaAsc':
                    $query->orderBy('fecha_limite_cumplimiento', 'asc');
                    break;
                case 'fechaDesc':
                    $query->orderBy('fecha_limite_cumplimiento', 'desc');
                    break;
                case 'vencido':
                    $query->where('fecha_limite_cumplimiento', '<', now());
                    break;
                case 'activo':
                    $query->where('fecha_limite_cumplimiento', '>=', now());
                    break;
                case 'proximoVencer':
                    $query->whereBetween('fecha_limite_cumplimiento', [now(), now()->addDays(30)]);
                    break;
            }
        } else {
            $query->orderBy('fecha_limite_cumplimiento', 'asc');
        }

        if ($request->ajax()) {
            return view('gestion_cumplimiento.detalles.partials.table', compact('requisitos'))->render();
        }
    }

    public function export() 
{
    return Excel::download(new RequisitosExport, 'requisitos.xlsx');
}


}
