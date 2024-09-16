<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function profile()
    {
        // Aquí va la lógica para mostrar el perfil del usuario
        return view('profile');
    }
}
