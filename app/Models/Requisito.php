<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Requisito extends Model
{
    use HasFactory;

    protected $table = 'requisitos';

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'id',
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

    /**
     * Relación con el modelo Notificacion.
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_notificacion', 'id_notificacion');
    }

    /**
     * Relación con el modelo Archivo.
     */
    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    /**
     * Scope para filtrar por año en la fecha de cumplimiento.
     */
    public function scopePorAno($query, $ano)
    {
        return $query->whereYear('fecha_limite_cumplimiento', $ano);
    }

    /**
     * Scope para filtrar los requisitos según el puesto del usuario.
     */
    public function scopePermitirVisualizacion($query, $user)
    {
        // Definir los puestos que pueden ver todos los registros
        $puestosPermitidos = [
            'Director Jurídico',
            'Directora General',
            'Jefa de Cumplimiento',
            'Director de Finanzas',
            'Gerente Jurídico',
            'Gerente de Atención a Usuarios',
            'Gerente de Operación',
            'Director de Operación',
        ];

        if (in_array($user->puesto, $puestosPermitidos)) {
            // Si el puesto del usuario está en la lista de permitidos, no filtrar por responsable
            return $query;
        } else {
            // Si el puesto no está permitido, filtrar por el puesto del usuario como responsable
            return $query->where('responsable', $user->puesto);
        }
    }
}
