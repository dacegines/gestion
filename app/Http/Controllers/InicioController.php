<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InicioController extends Controller
{
    /**
     * Muestra la página de inicio.
     */
    public function index()
    {
        return view('gestion_cumplimiento.inicio.index'); // Apunta a la vista ubicada en 'resources/views/inicio/inicio.blade.php'
    }
}
