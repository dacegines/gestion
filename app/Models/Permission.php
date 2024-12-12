<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    // Define la tabla asociada al modelo
    protected $table = 'permissions';

    // Define los atributos que pueden ser asignados masivamente
    protected $fillable = ['name', 'guard_name'];
}