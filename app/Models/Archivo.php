<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;

    protected $table = 'archivos';

    protected $fillable = [
        'requisito_id',
        'evidencia',
        'fecha_limite_cumplimiento',
        'nombre_archivo',
        'ruta_archivo',
        'fecha_subida',
        'usuario',
        'puesto',
    ];

    public function requisito()
    {
        return $this->belongsTo(Requisito::class);
    }
}
