<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class AdminUsersController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('model_has_permissions', 'users.id', '=', 'model_has_permissions.model_id')
            ->leftJoin('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->select(
                'users.id',
                'users.name as user_name',
                'users.email',
                'users.puesto',
                'roles.name as role_name',
                'roles.id as role_id', // Incluye el ID del rol
                'permissions.name as permission_name'
            )
            ->get();
    
        // Obtén todos los permisos ordenados por id de forma ascendente
        $permissions = DB::table('permissions')->select('id', 'name')->orderBy('id', 'asc')->get();
    
        // Obtén todos los roles ordenados por id de forma ascendente
        $roles = DB::table('roles')->select('id', 'name')->orderBy('id', 'asc')->get();
    
        // Obtén todos los usuarios ordenados por id de forma ascendente
        $allUsers = DB::table('users')->select('id', 'name', 'email')->orderBy('id', 'asc')->get();
    
        return view('gestion_cumplimiento.AdminUsuarios.index', compact('users', 'permissions', 'roles', 'allUsers'));
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

public function storePermission(Request $request)
{
    $request->validate([
        'model_id' => 'required|exists:users,id',
        'permission_id' => 'required|exists:permissions,id',
        'model_type' => 'required|string',
    ]);

    // Verificar si el registro ya existe
    $exists = DB::table('model_has_permissions')
        ->where('model_id', $request->input('model_id'))
        ->where('model_type', $request->input('model_type'))
        ->exists();

    if ($exists) {
        // Si ya existe, sobrescribir el permiso
        DB::table('model_has_permissions')
            ->where('model_id', $request->input('model_id'))
            ->where('model_type', $request->input('model_type'))
            ->update([
                'permission_id' => $request->input('permission_id'),
            ]);

        return redirect()->back()->with('success', 'El permiso se ha actualizado correctamente.');
    }

    // Si no existe, insertar un nuevo registro
    DB::table('model_has_permissions')->insert([
        'permission_id' => $request->input('permission_id'),
        'model_id' => $request->input('model_id'),
        'model_type' => $request->input('model_type'),
    ]);

    return redirect()->back()->with('success', 'Permiso asignado correctamente.');
}

public function storeRole(Request $request)
{
    $request->validate([
        'model_id' => 'required|exists:users,id',
        'role_id' => 'required|exists:roles,id',
        'model_type' => 'required|string',
    ]);

    // Verificar si el registro ya existe
    $exists = DB::table('model_has_roles')
        ->where('model_id', $request->input('model_id'))
        ->where('model_type', $request->input('model_type'))
        ->exists();

    if ($exists) {
        // Si ya existe, sobrescribir el rol
        DB::table('model_has_roles')
            ->where('model_id', $request->input('model_id'))
            ->where('model_type', $request->input('model_type'))
            ->update([
                'role_id' => $request->input('role_id'),
            ]);

        return redirect()->back()->with('success', 'El rol se ha actualizado correctamente.');
    }

    // Si no existe, insertar un nuevo registro
    DB::table('model_has_roles')->insert([
        'role_id' => $request->input('role_id'),
        'model_id' => $request->input('model_id'),
        'model_type' => $request->input('model_type'),
    ]);

    return redirect()->back()->with('success', 'Rol asignado correctamente.');
}

public function destroy($id)
{
    // Borrar al usuario
    DB::table('users')->where('id', $id)->delete();

    // Borrar los permisos asociados
    DB::table('model_has_permissions')->where('model_id', $id)->delete();

    // Borrar los roles asociados
    DB::table('model_has_roles')->where('model_id', $id)->delete();

    // Redirigir con un mensaje de éxito
    return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
}

public function update(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $request->user_id,
        'puesto' => 'required|string|max:255',
    ]);

    // Actualizar al usuario
    User::where('id', $request->user_id)->update([
        'name' => $request->name,
        'email' => $request->email,
        'puesto' => $request->puesto,
    ]);

    return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
}



}
