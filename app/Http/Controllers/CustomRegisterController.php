<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCreationRequest;

class CustomRegisterController extends Controller
{
    public function show()
    {
        return view('auth.register_new');
    }

    public function submitRequest(Request $request)
    {
        // Validar campos
        $request->validate([
            'name' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ], [
            'name.required' => 'El campo Nombre es obligatorio.',
            'puesto.required' => 'El campo Puesto es obligatorio.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.unique' => 'El correo ya está registrado en el sistema.',
        ]);

        // Enviar correo al administrador para procesar la creación de la cuenta
        Mail::to('daniel.cervantes@supervia.mx')->send(new AccountCreationRequest(
            $request->input('name'),
            $request->input('puesto'),
            $request->input('email')
        ));

        // Redirigir a la vista de formulario con un mensaje de éxito
        return redirect()->route('custom.register_new')->with('status', 'Solicitud de creación de cuenta enviada correctamente.');
    
    }
}
