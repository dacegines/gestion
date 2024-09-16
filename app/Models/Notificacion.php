<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    public function requisito()
    {
        return $this->belongsTo(Requisito::class);
    }
    public function notificaciones()
{
    return $this->hasMany(Notificacion::class, 'numero_evidencia', 'numero_evidencia');
}
}
