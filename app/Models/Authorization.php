<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Authorization extends Model
{
    // Define la tabla asociada si el nombre no sigue la convención
    protected $table = 'authorizations';

    // Permitir asignación masiva en estos campos
    protected $fillable = [
        'name',
        'guard_name'
    ];

    // Relación polimórfica con los modelos (User)
    public function models(): MorphToMany
    {
        return $this->morphToMany(User::class, 'model', 'model_has_authorizations', 'authorization_id', 'model_id');
    }
}
