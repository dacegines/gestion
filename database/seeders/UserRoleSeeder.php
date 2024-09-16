<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use App\Models\User;


class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asignar el rol de superusuario al primer usuario (puedes cambiar el ID)
        $superUser = User::find(1);
        $superUser->assignRole('superusuario');

        // Asignar el rol de admin al segundo usuario
        $admin = User::find(2);
        $admin->assignRole('admin');

        // Asignar el rol de usuario al tercer usuario
        $user = User::find(3);
        $user->assignRole('usuario');
    }
}
