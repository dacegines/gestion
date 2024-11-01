<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetRequest;
use App\Models\User;

class CustomPasswordResetController extends Controller
{
    public function show()
    {
        $names = User::select('name')->distinct()->pluck('name');
        $positions = User::select('puesto')->distinct()->pluck('puesto');
        $emails = User::select('email')->distinct()->pluck('email');
        $login_url = route('login'); // Define la URL de login aquí
    
        return view('auth.custom-password-reset', compact('names', 'positions', 'emails', 'login_url'));
    }

    public function submitRequest(Request $request)
{
    // Validar campos
    $request->validate([
        'name' => 'required|string|max:255',
        'puesto' => 'required|string|max:255',
        'email' => 'required|email|exists:users,email',
    ], [
        'name.required' => 'El campo Nombre es obligatorio.',
        'puesto.required' => 'El campo Puesto es obligatorio.',
        'email.required' => 'El campo Correo Electrónico es obligatorio.',
        'email.exists' => 'El correo no está registrado en el sistema.',
    ]);

    // Enviar correo o procesar la solicitud según tus requerimientos
    Mail::to('daniel.cervantes@supervia.mx')->send(new PasswordResetRequest(
        $request->input('name'),
        $request->input('puesto'),
        $request->input('email')
    ));

    // Redirigir a la vista de formulario con un mensaje de éxito
    return redirect()->route('custom.password.reset')->with('status', 'Solicitud enviada correctamente.');
}

}
