<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUsersController extends Controller
{
    public function index()
    {
    // Obtener la lista de usuarios con sus roles (si están configurados)
    $users = User::all(); 

    // Retornar la vista con los usuarios
    return view('gestion_cumplimiento.AdminUsuarios.index', compact('users'));
    }

    public function register(Request $request)
    {
        // Validar los datos
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8', // Mínimo de 8 caracteres
                'regex:/[A-Z]/', // Al menos una letra mayúscula
                'regex:/[0-9]/', // Al menos un número
                'regex:/[!@#$%^&*]/', // Al menos un carácter especial
                'confirmed', // Coincidencia de confirmación
            ],
        ]);

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'puesto' => $request->puesto,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->back()->with('success', 'Usuario registrado exitosamente.');
    }

    public function checkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $exists = User::where('email', $request->email)->exists();

    if ($exists) {
        return response()->json(['message' => 'El correo electrónico ya está registrado.', 'status' => 'error'], 200);
    }

    return response()->json(['message' => 'El correo electrónico está disponible.', 'status' => 'success'], 200);
}


}
