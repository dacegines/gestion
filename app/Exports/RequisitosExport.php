<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class RequisitosExport implements FromCollection
{
    protected $requisitos;

    public function __construct($requisitos)
    {
        $this->requisitos = $requisitos;
    }

    public function collection()
    {
        return $this->requisitos;
    }
}