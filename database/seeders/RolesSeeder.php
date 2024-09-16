<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $superUserRole = Role::create(['name' => 'superusuario']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'usuario']);

        // Asignar permisos a roles si es necesario
        // Ejemplo: $superUserRole->givePermissionTo(Permission::all());
        // Ejemplo: $adminRole->givePermissionTo('edit posts');

        // Nota: Puedes personalizar los permisos según tus necesidades.
    }
}
