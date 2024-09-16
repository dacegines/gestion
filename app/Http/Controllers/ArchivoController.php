<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado
use App\Mail\ArchivoSubidoMail; 
use App\Models\Requisito;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
class ArchivoController extends Controller
{
    public function subirArchivo(Request $request)
    {
        $validatedData = $request->validate([
            'archivo' => 'required|file|max:10240',
            'requisito_id' => 'required|integer',
            'evidencia' => 'required|string',
            'fecha_limite_cumplimiento' => 'required|date',
            'usuario' => 'required|string',
            'puesto' => 'required|string',
        ]);
    
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $fileName, 'public');
    
            $archivo = new Archivo();
            $archivo->nombre_archivo = $fileName;
            $archivo->ruta_archivo = $filePath;
            $archivo->requisito_id = $validatedData['requisito_id'];
            $archivo->evidencia = $validatedData['evidencia'];
            $archivo->fecha_limite_cumplimiento = $validatedData['fecha_limite_cumplimiento'];
            $archivo->usuario = $validatedData['usuario'];
            $archivo->puesto = $validatedData['puesto'];
            $archivo->fecha_subida = now(); // Asigna la fecha y hora actual
            $archivo->save();
    
            return response()->json(['success' => 'Archivo subido y guardado correctamente.']);
        }
    
        return response()->json(['error' => 'No se pudo subir el archivo.'], 422);
    }

    public function listarArchivos(Request $request)
    {
        $requisitoId = $request->input('requisito_id');
        $evidenciaId = $request->input('evidencia_id');
        $fechaLimite = $request->input('fecha_limite');
    
        // Obtén los archivos relacionados con el requisito, la evidencia y la fecha límite
        $archivos = Archivo::where('requisito_id', $requisitoId)
                            ->where('evidencia', $evidenciaId)
                            ->whereDate('fecha_limite_cumplimiento', $fechaLimite) // Filtrar por la fecha límite
                            ->get();
    
        // Devuelve los datos en formato JSON
        return response()->json(['archivos' => $archivos]);
    }
    
           

    public function eliminar(Request $request)
    {
        // Obtener el ID del archivo desde la solicitud
        $archivoId = $request->input('id');
        
        // Buscar el archivo en la base de datos
        $archivo = Archivo::find($archivoId);

        if ($archivo) {
            // Eliminar el archivo del almacenamiento
            if (Storage::exists('uploads/' . $archivo->nombre_archivo)) {
                Storage::delete('uploads/' . $archivo->nombre_archivo);
            }

            // Eliminar el registro de la base de datos
            $archivo->delete();

            return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente']);
        } else {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
        }
    }

    


    
    
}

