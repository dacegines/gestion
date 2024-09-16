<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear roles usando el campo 'name' y proporcionando 'guard_name'
        $role1 = Role::create(['name' => 'superUsuario', 'guard_name' => 'web']);
        $role2 = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $role3 = Role::create(['name' => 'escritor', 'guard_name' => 'web']);
        $role4 = Role::create(['name' => 'invitado', 'guard_name' => 'web']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para revertir, elimina los roles
        Role::where('name', 'superUsuario')->delete();
        Role::where('name', 'admin')->delete();
        Role::where('name', 'escritor')->delete();
        Role::where('name', 'invitado')->delete();
    }
};