<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;

use App\Models\Archivo;

use App\Mail\EstadoEvidenciaCambiado;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


use App\Mail\DatosEvidenciaMail;

use Illuminate\Support\Facades\Mail;

class ObligacionesController extends Controller
{
    /**
     * Muestra la vista con los requisitos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
    
        // Verificar si el usuario tiene un puesto definido
        if (!$user || !$user->puesto) {
            return redirect()->back()->withErrors(['error' => 'No se encontró el puesto del usuario autenticado']);
        }
    
        // Definir los puestos que pueden ver todos los registros
        $puestosPermitidos = [
            'Director Jurídico',
            'Directora General',
            'Jefa de Cumplimiento',
            'Director de Finanzas',
            'Gerente Jurídico',
            'Gerente de Atención a Usuarios',
            'Gerente de Operación',
            'Director de Operación',
        ];
    
        // Comprobar si el puesto del usuario está en la lista de puestos permitidos
        if (in_array($user->puesto, $puestosPermitidos)) {
            // Si el usuario tiene un puesto permitido, obtener todos los requisitos
            $requisitos = Requisito::all();
        } else {
            // Si no, filtrar los requisitos donde el responsable coincida con el puesto del usuario
            $requisitos = Requisito::where('responsable', $user->puesto)->get();
        }
    
        // Filtrar los requisitos para omitir los que no tienen un responsable asignado
        $requisitos = $requisitos->filter(function ($requisito) {
            return !empty($requisito->responsable);
        });
    
        // Calcular el total de avance para cada requisito
        foreach ($requisitos as $requisito) {
            $requisito->total_avance = $this->getTotalAvance($requisito->numero_requisito);
        }
    
        // Pasar los requisitos y el puesto del usuario a la vista
        return view('gestion_cumplimiento.obligaciones.index', compact('requisitos', 'user'));
    }
    
    

    /**
     * Calcular el total de avance para un requisito dado.
     */
    public function getTotalAvance($numero_requisito)
    {
        // Consulta para sumar los valores de la columna "avance" y redondear a 2 dígitos decimales
        $total_avance = Requisito::where('numero_requisito', $numero_requisito)                        
                            ->sum('avance');

        // Redondea a 2 dígitos decimales
        return round($total_avance, 2);
    }

    public function getDetallesEvidencia(Request $request)
    {
        $evidenciaId = $request->input('evidencia_id');

        
        $detalle = Requisito::where('numero_evidencia', $evidenciaId)->first();
        
        if ($detalle) {

                        // Establecer la localización en español
                   
            // Suponiendo que tienes varias fechas asociadas a la evidencia, obténlas como un array
            $fechas_limite = Requisito::where('numero_evidencia', $evidenciaId)
                ->pluck('fecha_limite_cumplimiento')
                ->map(function($fecha) {
                    return \Carbon\Carbon::parse($fecha)->format('d/m/Y');
                })
                ->toArray();
            
            return response()->json([
                
                'evidencia' => $detalle->evidencia,
                'periodicidad' => $detalle->periodicidad,
                'responsable' => $detalle->responsable,
                'fechas_limite_cumplimiento' => $fechas_limite, // Enviar el array de fechas
                'origen_obligacion' => $detalle->origen_obligacion,
                'clausula_condicionante_articulo' => $detalle->clausula_condicionante_articulo,
                'id_notificacion' => $detalle->id_notificacion,
            ]);
        } else {
            return response()->json(['error' => 'No se encontró la evidencia'], 404);
        }
    }
    

    public function obtenerNotificaciones(Request $request)
    {       
        // Obtener el id_notificacion desde el request
        $idNotificacion = $request->input('id_notificaciones');
        
        // Realizar la consulta utilizando el campo `id_notificacion`
        $notificaciones = DB::table('notificaciones')
            ->where('id_notificacion', $idNotificacion) // Filtrar por `id_notificacion`
            ->distinct() // Asegurar que solo se devuelvan valores únicos
            ->pluck('nombre') // Obtener solo los nombres de las notificaciones
            ->toArray();
    
        // Retornar las notificaciones como respuesta JSON
        return response()->json($notificaciones);
    }


    public function obtenerTablaNotificaciones(Request $request)
    {
        $idNotificaciones = $request->input('id_notificaciones');
        
        // Realizar la consulta utilizando el campo `id_notificacion`
        $notificaciones = DB::table('notificaciones')
            ->where('id_notificacion', $idNotificaciones) // Filtrar por `id_notificacion`
            ->get(['nombre', 'tipo_notificacion']) // Obtener solo las columnas necesarias
            ->toArray();
        
        // Añadir lógica para definir los colores y días según el tipo de notificación
        $resultado = array_map(function ($notificacion) {
            $dias = '';
            $tipoNotificacion = '';
            $estilo = '';
    
            switch ($notificacion->tipo_notificacion) {
                case 'primera_notificacion':
                    $dias = '30 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '1era Notificación';
                    $estilo = 'style="background-color: #90ee90; color: black;"';  // Suavizando el verde
                    break;
                case 'segunda_notificacion':
                    $dias = '15 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '2da Notificación';
                    $estilo = 'style="background-color: #ffff99; color: black;"';  // Suavizando el amarillo
                    break;
                case 'tercera_notificacion':
                    $dias = '5 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '3era Notificación';
                    $estilo = 'style="background-color: #ffcc99; color: black;"';  // Suavizando el naranja
                    break;
                case 'notificacion_carga_vobo':
                    $dias = 'Inmediato antes de la fecha de vencimiento';
                    $tipoNotificacion = '4ta Notificación';
                    $estilo = 'style="background-color: #ff9999; color: black;"';  // Suavizando el rojo
                    break;
            }
    
            return [
                'nombre' => $notificacion->nombre,
                'tipo' => $tipoNotificacion,
                'dias' => $dias,
                'estilo' => $estilo
            ];
        }, $notificaciones);
    
        return response()->json($resultado);
    }

    public function obtenerDetalleEvidencia(Request $request)
    {
        $evidenciaId = $request->input('evidencia_id');
        $detalleId = $request->input('detalle_id');
        $requisitoId = $request->input('requisito_id');
    
        // Buscar el detalle en la tabla Requisito
        $detalle = Requisito::where('id', $detalleId)
                    ->where('numero_evidencia', $evidenciaId)
                    ->first();
    
        // Buscar el nombre de archivo relacionado en la tabla Archivos
        $archivo = Archivo::where('requisito_id', $requisitoId)->first();
    
        if ($detalle) {
            return response()->json([
                'id' => $detalle->id,
                'numero_requisito' => $evidenciaId,
                'nombre' => $detalle->nombre,
                'evidencia' => $detalle->evidencia,
                'periodicidad' => $detalle->periodicidad,
                'responsable' => $detalle->responsable,
                'fecha_limite_cumplimiento' => $detalle->fecha_limite_cumplimiento
                    ? \Carbon\Carbon::parse($detalle->fecha_limite_cumplimiento)->format('d/m/Y')
                    : null,
                'origen_obligacion' => $detalle->origen_obligacion,
                'clausula_condicionante_articulo' => $detalle->clausula_condicionante_articulo,
                'nombre_archivo' => $archivo ? $archivo->nombre_archivo : null,  // Aquí se incluye el nombre del archivo
            ]);
        } else {
            return response()->json(['error' => 'Detalle no encontrado'], 404);
        }
    }


    
    public function cambiarEstado(Request $request)
    {
        $requisitoId = $request->input('id');
        
        // Buscar el requisito por ID
        $requisito = Requisito::find($requisitoId);
        
        if (!$requisito) {
            return response()->json(['error' => 'Requisito no encontrado'], 404);
        }
    
        // Alternar el estado del campo 'approved'
        $requisito->approved = !$requisito->approved; 
        $requisito->save();
    
        // Obtener el correo del responsable desde la base de datos
        $emailResponsables = [];
    
        if ($requisito->email) {
            $emailResponsables[] = $requisito->email; // Añadir el email del responsable al array
        }
    
        // Obtener los correos adicionales desde la tabla responsables
        $otrosCorreos = DB::table('responsables')
            ->distinct()
            ->whereIn('puesto', ['Gerente Jurídico', 'Director Jurídico', 'Jefa de Cumplimiento'])
            ->pluck('email')
            ->toArray();
    
        // Combinar los correos del responsable y los adicionales
        $destinatarios = array_merge($emailResponsables, $otrosCorreos);
    
        // Verificar que haya al menos un destinatario

            // Enviar correo a los responsables y a los destinatarios adicionales
            Mail::to($destinatarios)->send(new EstadoEvidenciaCambiado(
                $requisito->nombre,
                $requisito->evidencia,
                $requisito->periodicidad,
                $requisito->responsable,
                \Carbon\Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d/m/Y'),
                $requisito->origen_obligacion,
                $requisito->clausula_condicionante_articulo,
                $requisito->approved
            ));

    
        return response()->json(['success' => true, 'approved' => $requisito->approved]);
    }
    

public function store()
{


    return view('gestion_cumplimiento.obligaciones.create');
}


public function obtenerRequisitoDetalles(Request $request)
{
    $requisitoId = $request->input('requisito_id');
    $evidenciaId = $request->input('evidencia_id');

    $porcentaje = 0;

    // Recupera el requisito correspondiente de la base de datos
    $requisito = Requisito::find($requisitoId);

    if ($requisito) {
        // Puedes personalizar los datos que deseas devolver
        return response()->json([
            
            'numero_requisito'=> $requisito->numero_requisito,
            'nombre' => $requisito->nombre,
            'requisito' => $requisito->requisito,
            'sub_requisito' => $requisito->sub_requisito,
            'periodicidad' => $requisito->periodicidad,
            'numero_evidencia' => $evidenciaId,
            'evidencia' => $requisito->evidencia,
            //'fecha_limite_cumplimiento' => $requisito->fecha_limite_cumplimiento,
            'responsable' => $requisito->responsable,
            'origen_obligacion' => $requisito->origen_obligacion,
            'clausula_condicionante_articulo' => $requisito->clausula_condicionante_articulo,
            'id_notificaciones' => $requisito->id_notificaciones,
            
            // Agrega otros campos según lo necesites
        ]);
    } else {
        return response()->json(['error' => 'Requisito no encontrado'], 404);
    }
}



public function guardarRequisito(Request $request)
{
    // Crear o actualizar el registro en la base de datos sin validación
    
    $requisito = Requisito::updateOrCreate(
        ['id' => $request->input('requisito_id')],
        [
            'numero_requisito' => $request->input('numero_requisito'),
            'nombre' => $request->input('nombre'),
            'requisito' => $request->input('requisito'),
            'sub_requisito' => $request->input('sub_requisito'),
            'periodicidad' => $request->input('periodicidad'),
            'numero_evidencia' => $request->input('numero_evidencia'),
            'evidencia' => $request->input('evidencia'),

            'porcentaje' => $request->input('porcentaje'),
            'avance' => $request->input('avance'),
            'fecha_limite_cumplimiento' => $request->input('fecha_limite_cumplimiento'),
            'responsable' => $request->input('responsable'),
            'origen_obligacion' => $request->input('origen_obligacion'),
            'clausula_condicionante_articulo' => $request->input('clausula_condicionante_articulo'),
            'id_notificaciones' => $request->input('id_notificaciones'),     
            'approved' => $request->input('approved'),     
            
        ]
    );

    // Devolver una respuesta exitosa
    return response()->json(['success' => true, 'requisito' => $requisito]);
}


public function obtenerEstadoApproved(Request $request)
{
    // Validar que se recibe el id del requisito
    $request->validate([
        'id' => 'required|integer|exists:requisitos,id'
    ]);

    // Obtener el requisito a partir del ID
    $requisito = Requisito::find($request->id);

    // Retornar el estado 'approved' como respuesta
    return response()->json(['approved' => $requisito->approved]);
}

public function enviarCorreoDatosEvidencia(Request $request)
{
    $datos = $request->all();

    // Verificar que los datos requeridos estén presentes
    if (!isset($datos['evidencia']) || empty($datos['evidencia'])) {
        return response()->json(['error' => 'Falta el campo evidencia'], 400);
    }

    // Buscar el requisito en la base de datos para obtener el correo del responsable
    $requisito = Requisito::where('evidencia', $datos['evidencia'])->first();

    if (!$requisito) {
        return response()->json(['error' => 'No se encontró el requisito asociado a la evidencia'], 404);
    }

    if (!$requisito->email) {
        return response()->json(['error' => 'No se encontró el correo del responsable'], 400);
    }

    // Asignar el nombre del requisito desde la base de datos
    $datos['nombre'] = $requisito->nombre;

    // Correo del responsable desde la base de datos
    $destinatarioResponsable = $requisito->email;

    // Obtener los correos adicionales desde la tabla responsables
    $otrosCorreos = DB::table('responsables')
        ->distinct()
        ->whereIn('puesto', ['Gerente Jurídico', 'Director Jurídico', 'Jefa de Cumplimiento'])
        ->pluck('email')
        ->toArray();

    // Crear la lista de destinatarios
    $destinatarios = array_merge([$destinatarioResponsable], $otrosCorreos);

    // Enviar el correo a todos los destinatarios
    Mail::to($destinatarios)->send(new DatosEvidenciaMail($datos));

    return response()->json(['success' => true, 'message' => 'Correo enviado correctamente']);
}





public function actualizarPorcentaje(Request $request)
{
    $requisitoId = $request->input('id');
    
    // Buscar el requisito por ID
    $requisito = Requisito::find($requisitoId);

    if (!$requisito) {
        return response()->json(['error' => 'Requisito no encontrado'], 404);
    }

    // Si el porcentaje es 100, lo cambia a 0; si es 0, lo cambia a 100
    $requisito->porcentaje = $requisito->porcentaje == 100 ? 0 : 100;
    $requisito->save();

    return response()->json(['success' => true, 'nuevo_porcentaje' => $requisito->porcentaje]);
}

public function actualizarPorcentajeSuma(Request $request)
{
    // Validar el número de requisito y el id del requisito recibido
    $request->validate([
        'numero_requisito' => 'required|integer',
        'requisito_id' => 'required|integer',
    ]);

    $numeroRequisito = $request->input('numero_requisito');
    $requisitoId = $request->input('requisito_id');

    // Obtener el valor actual de la columna 'avance' para el registro con el id dado
    $avanceActual = DB::table('requisitos')
                      ->where('id', $requisitoId)
                      ->value('avance');

    // Si 'avance' ya es mayor que 0, asignar un valor de 0
    if ($avanceActual > 0) {
        $nuevoAvance = 0;
    } else {
        // Contar los registros en la tabla 'requisitos' donde 'numero_requisito' coincide
        $conteo = DB::table('requisitos')
                    ->where('numero_requisito', $numeroRequisito)
                    ->count();

        // Calcular el porcentaje que representa un solo registro, redondeado a dos decimales
        $nuevoAvance = ($conteo > 0) ? round(100 / $conteo, 2) : 0;
    }

    // Actualizar la columna 'avance' en la base de datos para el requisito con el id dado
    DB::table('requisitos')
        ->where('id', $requisitoId)
        ->update([
            'avance' => $nuevoAvance
        ]);

    // Devolver el conteo y el nuevo avance en formato JSON
    return response()->json([
        'conteo' => $conteo ?? 0,
        'nuevo_avance' => $nuevoAvance,
    ]);
}

public function validarArchivos(Request $request)
{
    // Validar los datos de entrada
    $validatedData = $request->validate([
        'requisito_id' => 'required|integer',
        'numero_evidencia' => 'required|string',
        'fecha_limite_cumplimiento' => 'required|date',
    ]);

    $requisitoId = $validatedData['requisito_id'];
    $numeroEvidencia = $validatedData['numero_evidencia'];
    $fechaLimiteCumplimiento = $validatedData['fecha_limite_cumplimiento'];

    try {
        // Buscar en la tabla archivos si existe un registro con los parámetros proporcionados
        $archivo = DB::table('archivos')
            ->where('requisito_id', $requisitoId)
            ->where('evidencia', $numeroEvidencia)  // Corregido aquí
            ->where('fecha_limite_cumplimiento', $fechaLimiteCumplimiento)
            ->first();

        if ($archivo) {
            return response()->json(['exists' => true, 'message' => 'Archivo encontrado.'], 200);
        } else {
            return response()->json(['exists' => false, 'message' => 'No se encontraron archivos con los datos proporcionados.'], 404);
        }
    } catch (\Exception $e) {
        // Manejo de excepciones y log de errores
        Log::error('Error al validar archivos: ' . $e->getMessage());
        return response()->json(['error' => 'Error al validar archivos.'], 500);
    }
}

    // Nuevo método para verificar los archivos
    public function verificarArchivos(Request $request)
    {
        $requisitoId = $request->input('requisito_id');
        $fechaLimiteInput = $request->input('fecha_limite_cumplimiento');

            // Convertir la fecha de "d/m/Y" a "Y-m-d" usando Carbon
            $fechaLimite = Carbon::createFromFormat('d/m/Y', $fechaLimiteInput)->format('Y-m-d');
    
            // Contar los registros en la tabla 'archivos' que coincidan con 'requisito_id' y 'fecha_limite_cumplimiento'
            $conteo = Archivo::where('requisito_id', $requisitoId)
                ->where('fecha_limite_cumplimiento', $fechaLimite)
                ->count();
    
            // Retornar el conteo real de registros encontrados
            return response()->json(['conteo' => $conteo]);
    

    }
    




}







