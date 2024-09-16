<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;

    protected $table = 'requisitos';

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_notificacion', 'id_notificacion');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    protected $fillable = [
        'id', // Si necesitas actualizar el id
        'nombre',
        'requisito',
        'sub_requisito',
        'periodicidad',
        'numero_evidencia',
        'evidencia',
        'porcentaje',
        'avance',
        'fecha_limite_cumplimiento',
        'responsable',
        'email',
        'origen_obligacion',
        'clausula_condicionante_articulo',
    ];
}
