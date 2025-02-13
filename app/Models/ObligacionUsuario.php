<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObligacionUsuario extends Model
{
    use HasFactory;

    protected $table = 'obligacion_usuario'; // Nombre exacto de la tabla

    protected $fillable = ['user_id', 'numero_requisito', 'view']; // Campos permitidos para asignación masiva

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
