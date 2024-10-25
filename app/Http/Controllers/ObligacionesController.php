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
    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->puesto) {
                $this->logWarning('Usuario autenticado sin puesto definido', ['user_id' => $user->id ?? null]);
                return back()->withErrors(['error' => 'No se encontró el puesto del usuario autenticado']);
            }
    
            $currentYear = Carbon::now()->year;
            $requisitos = $this->obtenerRequisitosConAvance($currentYear, $user);
    
            $this->logInfo('Requisitos cargados correctamente', ['user_id' => $user->id, 'total_requisitos' => $requisitos->count()]);
    
            // Definir los puestos excluidos para pasar a la vista
            $puestosExcluidos = [
                'Director Jurídico',
                'Directora General',
                'Jefa de Cumplimiento',
                'Director de Finanzas',
                'Director de Operación'
                
            ];
    
            return view('gestion_cumplimiento.obligaciones.index', compact('requisitos', 'user', 'currentYear', 'puestosExcluidos'));
    
        } catch (\Exception $e) {
            $this->logError('Error al cargar las obligaciones', ['error' => $e->getMessage(), 'user_id' => $user->id ?? null]);
            return back()->withErrors(['error' => 'Ocurrió un error al cargar las obligaciones.']);
        }
    }
    

    private function obtenerRequisitosConAvance($year, $user)
    {
        return Requisito::with('archivos')
            ->porAno($year)
            ->permitirVisualizacion($user)
            ->get()
            ->filter(fn($requisito) => !empty($requisito->responsable))
            ->each(fn($requisito) => $requisito->total_avance = $this->getTotalAvance($requisito->numero_requisito));
    }

    public function getTotalAvance($numero_requisito)
    {
        try {
            if (empty($numero_requisito)) {
                $this->logWarning('Número de requisito vacío al intentar calcular el total de avance.');
                return 0;
            }

            $total_avance = Requisito::where('numero_requisito', $numero_requisito)
                                     ->sum('avance');
            return round($total_avance, 2);
        } catch (\Exception $e) {
            $this->logError('Error al calcular el total de avance', ['numero_requisito' => $numero_requisito, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    public function getDetallesEvidencia(Request $request)
    {
        try {
            $evidenciaId = $request->input('evidencia_id');
            if (empty($evidenciaId)) {
                $this->logWarning('ID de evidencia vacío al intentar obtener los detalles de evidencia.');
                return response()->json(['error' => 'ID de evidencia no proporcionado'], 400);
            }

            $detalle = Requisito::where('numero_evidencia', $evidenciaId)->first();
            if ($detalle) {
                $fechas_limite = Requisito::where('numero_evidencia', $evidenciaId)
                    ->pluck('fecha_limite_cumplimiento')
                    ->map(fn($fecha) => Carbon::parse($fecha)->format('d/m/Y'))
                    ->toArray();

                $this->logInfo('Detalles de evidencia obtenidos', ['evidencia_id' => $evidenciaId]);
                return response()->json([
                    'evidencia' => $detalle->evidencia,
                    'periodicidad' => $detalle->periodicidad,
                    'responsable' => $detalle->responsable,
                    'fechas_limite_cumplimiento' => $fechas_limite,
                    'origen_obligacion' => $detalle->origen_obligacion,
                    'clausula_condicionante_articulo' => $detalle->clausula_condicionante_articulo,
                    'id_notificacion' => $detalle->id_notificacion,
                    'condicion' => $detalle->condicion,
                ]);
            } else {
                $this->logWarning('No se encontró la evidencia', ['evidencia_id' => $evidenciaId]);
                return response()->json(['error' => 'No se encontró la evidencia'], 404);
            }
        } catch (\Exception $e) {
            $this->logError('Error al obtener los detalles de evidencia', ['evidencia_id' => $evidenciaId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al obtener los detalles de la evidencia'], 500);
        }
    }

    public function obtenerNotificaciones(Request $request)
    {
        try {
            $idNotificacion = $request->input('id_notificaciones');
            if (empty($idNotificacion)) {
                $this->logWarning('ID de notificación vacío al intentar obtener notificaciones.');
                return response()->json(['error' => 'ID de notificación no proporcionado'], 200);
            }

            $notificaciones = DB::table('notificaciones')
                ->where('id_notificacion', $idNotificacion)
                ->distinct()
                ->pluck('nombre')
                ->toArray();

            if (empty($notificaciones)) {
                $this->logInfo('No se encontraron notificaciones', ['id_notificacion' => $idNotificacion]);
                return response()->json([], 200);
            }

            $this->logInfo('Notificaciones obtenidas correctamente', ['id_notificacion' => $idNotificacion]);
            return response()->json($notificaciones);

        } catch (\Exception $e) {
            $this->logError('Error al obtener notificaciones', ['id_notificacion' => $idNotificacion, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al obtener las notificaciones'], 500);
        }
    }

    public function obtenerTablaNotificaciones(Request $request)
    {
        $idNotificaciones = $request->input('id_notificaciones');
        $notificaciones = DB::table('notificaciones')
            ->where('id_notificacion', $idNotificaciones)
            ->get(['nombre', 'tipo_notificacion'])
            ->toArray();

        $resultado = array_map(function ($notificacion) {
            $dias = '';
            $tipoNotificacion = '';
            $estilo = '';

            switch ($notificacion->tipo_notificacion) {
                case 'primera_notificacion':
                    $dias = '30 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '1era Notificación';
                    $estilo = 'style="background-color: #90ee90; color: black;"';
                    break;
                case 'segunda_notificacion':
                    $dias = '15 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '2da Notificación';
                    $estilo = 'style="background-color: #ffff99; color: black;"';
                    break;
                case 'tercera_notificacion':
                    $dias = '5 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '3era Notificación';
                    $estilo = 'style="background-color: #ffcc99; color: black;"';
                    break;
                case 'notificacion_carga_vobo':
                    $dias = 'Inmediato antes de la fecha de vencimiento';
                    $tipoNotificacion = '4ta Notificación';
                    $estilo = 'style="background-color: #ff9999; color: black;"';
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

    public function cambiarEstado(Request $request)
    {
        try {
            $requisitoId = $request->input('id');
            if (empty($requisitoId)) {
                $this->logWarning('ID de requisito no proporcionado.');
                return response()->json(['error' => 'ID de requisito no proporcionado'], 400);
            }

            $requisito = Requisito::find($requisitoId);
            if (!$requisito) {
                $this->logInfo('Requisito no encontrado', ['requisito_id' => $requisitoId]);
                return response()->json(['error' => 'Requisito no encontrado'], 404);
            }

            $requisito->approved = !$requisito->approved;
            $requisito->save();

            $this->logInfo('Estado del requisito cambiado', ['requisito_id' => $requisitoId, 'nuevo_estado' => $requisito->approved]);

            $emailResponsables = !empty($requisito->email) ? [$requisito->email] : [];
            $otrosCorreos = DB::table('responsables')
                ->distinct()
                ->whereIn('puesto', ['Gerente Jurídico', 'Director Jurídico', 'Jefa de Cumplimiento'])
                ->pluck('email')
                ->toArray();

            $destinatarios = array_merge($emailResponsables, $otrosCorreos);

            if (count($destinatarios) > 0) {
                Mail::to($destinatarios)->send(new EstadoEvidenciaCambiado(
                    $requisito->nombre,
                    $requisito->evidencia,
                    $requisito->periodicidad,
                    $requisito->responsable,
                    Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d/m/Y'),
                    $requisito->origen_obligacion,
                    $requisito->clausula_condicionante_articulo,
                    $requisito->approved
                ));
                $this->logInfo('Correo enviado correctamente', ['destinatarios' => $destinatarios]);
            } else {
                $this->logWarning('No se encontraron destinatarios para el envío de correo', ['requisito_id' => $requisitoId]);
            }

            return response()->json(['success' => true, 'approved' => $requisito->approved]);

        } catch (\Exception $e) {
            $this->logError('Error al cambiar el estado del requisito', ['requisito_id' => $requisitoId ?? 'N/A', 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al cambiar el estado del requisito'], 500);
        }
    }

    public function obtenerEstadoApproved(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:requisitos,id'
            ]);

            $requisito = Requisito::find($validatedData['id']);
            $this->logInfo('Estado "approved" obtenido correctamente', ['requisito_id' => $requisito->id, 'approved' => $requisito->approved]);

            return response()->json(['approved' => $requisito->approved]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al obtener el estado "approved"', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al obtener el estado "approved"', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al obtener el estado aprobado'], 500);
        }
    }

    public function enviarCorreoDatosEvidencia(Request $request)
{
    try {
        // Validar que se haya recibido la evidencia
        $request->validate([
            'evidencia' => 'required|string'
        ]);

        // Obtener todos los datos del request
        $datos = $request->all();

        // Buscar el requisito por la evidencia
        $requisito = Requisito::where('evidencia', $datos['evidencia'])->first();

        // Si no se encuentra el requisito, retornar error
        if (!$requisito) {
            $this->logWarning('No se encontró el requisito asociado a la evidencia', ['evidencia' => $datos['evidencia']]);
            return response()->json(['error' => 'No se encontró el requisito asociado a la evidencia'], 404);
        }

        // Si no se encuentra el email del responsable, retornar error
        if (!$requisito->email) {
            $this->logWarning('Correo del responsable no encontrado', ['requisito_id' => $requisito->id]);
            return response()->json(['error' => 'No se encontró el correo del responsable'], 400);
        }

        // Obtener los destinatarios
        $destinatarioResponsable = $requisito->email;
        $otrosCorreos = DB::table('responsables')
            ->distinct()
            ->whereIn('puesto', ['Gerente Jurídico', 'Director Jurídico', 'Jefa de Cumplimiento'])
            ->pluck('email')
            ->toArray();

        // Fusionar correos
        $destinatarios = array_merge([$destinatarioResponsable], $otrosCorreos);

        // Enviar el correo usando el Mailable DatosEvidenciaMail, pasando cada parámetro individualmente
        Mail::to($destinatarios)->send(new DatosEvidenciaMail(
            $requisito->nombre,  // Nombre del requisito
            $requisito->evidencia,  // Evidencia
            $requisito->periodicidad,  // Periodicidad
            $requisito->responsable,  // Responsable
            $requisito->fecha_limite_cumplimiento,  // Fecha límite de cumplimiento
            $requisito->origen_obligacion,  // Origen de la obligación
            $requisito->clausula_condicionante_articulo  // Cláusula, condicionante, o artículo
        ));

        // Log de éxito en el envío de correo
        $this->logInfo('Correo enviado correctamente', ['destinatarios' => $destinatarios, 'evidencia' => $datos['evidencia']]);
        return response()->json(['success' => true, 'message' => 'Correo enviado correctamente']);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log de error de validación
        $this->logWarning('Error de validación al enviar correo de datos de evidencia', ['errors' => $e->errors()]);
        return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
    } catch (\Exception $e) {
        // Log de error inesperado
        $this->logError('Error inesperado al enviar correo de datos de evidencia', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Ocurrió un error al enviar el correo'], 500);
    }
}


    public function actualizarPorcentaje(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:requisitos,id',
            ]);

            $requisito = Requisito::find($validatedData['id']);
            if (!$requisito) {
                $this->logWarning('Requisito no encontrado', ['requisito_id' => $validatedData['id']]);
                return response()->json(['error' => 'Requisito no encontrado'], 404);
            }

            $requisito->porcentaje = $requisito->porcentaje == 100 ? 0 : 100;
            $requisito->save();

            $this->logInfo('Porcentaje actualizado correctamente', [
                'requisito_id' => $requisito->id,
                'nuevo_porcentaje' => $requisito->porcentaje,
            ]);

            return response()->json(['success' => true, 'nuevo_porcentaje' => $requisito->porcentaje]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al actualizar el porcentaje', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al actualizar el porcentaje', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al actualizar el porcentaje'], 500);
        }
    }

    public function actualizarPorcentajeSuma(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'numero_requisito' => 'required|integer',
                'requisito_id' => 'required|integer|exists:requisitos,id',
            ]);

            $numeroRequisito = $validatedData['numero_requisito'];
            $requisitoId = $validatedData['requisito_id'];
            $avanceActual = DB::table('requisitos')
                ->where('id', $requisitoId)
                ->value('avance');

            $nuevoAvance = ($avanceActual > 0) ? 0 : round(100 / DB::table('requisitos')
                ->where('numero_requisito', $numeroRequisito)
                ->count(), 2);

            DB::table('requisitos')
                ->where('id', $requisitoId)
                ->update(['avance' => $nuevoAvance]);

            $this->logInfo('Avance actualizado', [
                'requisito_id' => $requisitoId,
                'numero_requisito' => $numeroRequisito,
                'nuevo_avance' => $nuevoAvance,
            ]);

            return response()->json([
                'conteo' => DB::table('requisitos')->where('numero_requisito', $numeroRequisito)->count(),
                'nuevo_avance' => $nuevoAvance,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al actualizar el avance', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al actualizar el avance', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al actualizar el avance'], 500);
        }
    }

    public function validarArchivos(Request $request)
    {
        $validatedData = $request->validate([
            'requisito_id' => 'required|integer|exists:requisitos,id',
            'numero_evidencia' => 'required|string',
            'fecha_limite_cumplimiento' => 'required|date',
        ]);

        $requisitoId = $validatedData['requisito_id'];
        $numeroEvidencia = $validatedData['numero_evidencia'];
        $fechaLimiteCumplimiento = $validatedData['fecha_limite_cumplimiento'];

        try {
            $archivo = DB::table('archivos')
                ->where('requisito_id', $requisitoId)
                ->where('evidencia', $numeroEvidencia)
                ->whereDate('fecha_limite_cumplimiento', $fechaLimiteCumplimiento)
                ->first();

            if ($archivo) {
                $this->logInfo('Archivo validado exitosamente', ['archivo_id' => $archivo->id, 'requisito_id' => $requisitoId]);
                return response()->json(['exists' => true, 'message' => 'Archivo encontrado.'], 200);
            } else {
                $this->logInfo('No se encontraron archivos con los datos proporcionados', ['requisito_id' => $requisitoId, 'numero_evidencia' => $numeroEvidencia]);
                return response()->json(['exists' => false, 'message' => 'No se encontraron archivos con los datos proporcionados.'], 404);
            }
        } catch (\Exception $e) {
            $this->logError('Error al validar archivos', ['requisito_id' => $requisitoId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al validar archivos.'], 500);
        }
    }

    public function verificarArchivos(Request $request)
    {
        $validatedData = $request->validate([
            'requisito_id' => 'required|integer|exists:requisitos,id',
            'fecha_limite_cumplimiento' => 'required|date_format:d/m/Y',
        ]);

        try {
            $requisitoId = $validatedData['requisito_id'];
            $fechaLimite = Carbon::createFromFormat('d/m/Y', $validatedData['fecha_limite_cumplimiento'])->format('Y-m-d');
            $conteo = Archivo::where('requisito_id', $requisitoId)
                ->whereDate('fecha_limite_cumplimiento', $fechaLimite)
                ->count();

            $this->logInfo('Verificación de archivos completada', [
                'requisito_id' => $requisitoId,
                'fecha_limite_cumplimiento' => $fechaLimite,
                'conteo' => $conteo,
            ]);

            return response()->json(['conteo' => $conteo]);
        } catch (\Exception $e) {
            $this->logError('Error al verificar archivos', [
                'requisito_id' => $request->input('requisito_id'),
                'fecha_limite_cumplimiento' => $request->input('fecha_limite_cumplimiento'),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al verificar archivos.'], 500);
        }
    }

    private function logInfo($message, $context = [])
    {
        Log::info($message, $context);
    }

    private function logWarning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    private function logError($message, $context = [])
    {
        Log::error($message, $context);
    }

    public function filtrarObligaciones(Request $request)
    {
        $year = $request->input('year');
        $user = Auth::user();
    
        if (!$user || !$user->puesto) {
            return back()->withErrors(['error' => 'No se encontró el puesto del usuario autenticado']);
        }
    
        $requisitos = Requisito::porAno($year)
            ->permitirVisualizacion($user)
            ->get()
            ->filter(fn($requisito) => !empty($requisito->responsable))
            ->each(fn($requisito) => $requisito->total_avance = $this->getTotalAvance($requisito->numero_requisito));
    
        return view('gestion_cumplimiento.obligaciones.index', compact('requisitos', 'user', 'year'));
    }

    public function obtenerDetalleEvidencia(Request $request)
{
    try {
        $evidenciaId = $request->input('evidencia_id');
        $detalleId = $request->input('detalle_id');
        $requisitoId = $request->input('requisito_id');

        if (empty($evidenciaId) || empty($detalleId) || empty($requisitoId)) {
            Log::warning('Datos de entrada faltantes o inválidos', [
                'evidencia_id' => $evidenciaId,
                'detalle_id' => $detalleId,
                'requisito_id' => $requisitoId
            ]);
            return response()->json(['error' => 'Datos de entrada faltantes o inválidos'], 400);
        }

        $detalle = Requisito::where('id', $detalleId)
                    ->where('numero_evidencia', $evidenciaId)
                    ->first();

        if (!$detalle) {
            Log::info('Detalle no encontrado', ['detalle_id' => $detalleId, 'evidencia_id' => $evidenciaId]);
            return response()->json(['error' => 'Detalle no encontrado'], 404);
        }

        $archivo = Archivo::where('requisito_id', $requisitoId)->first();

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
            'nombre_archivo' => $archivo ? $archivo->nombre_archivo : null,
            'condicion' => $detalle->condicion,
        ]);

    } catch (\Exception $e) {
        Log::error('Error al obtener el detalle de evidencia', [
            'error' => $e->getMessage()
        ]);
        return response()->json(['error' => 'Ocurrió un error al obtener el detalle de la evidencia'], 500);
    }
}

    

}
