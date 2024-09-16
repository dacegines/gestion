<?php

namespace App\Exports;

use App\Models\Requisito;
use Maatwebsite\Excel\Concerns\FromCollection;

class RequisitosExport implements FromCollection
{
    public function collection()
    {
        return Requisito::all(); // O aplica los filtros que desees
    }
}
