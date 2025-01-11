<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTipoNotificacionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actualizar registros de la columna tipo_notificacion
        DB::table('notificaciones')
            ->where('tipo_notificacion', 'notificacion_carga_vobo')
            ->update(['tipo_notificacion' => 'tercera_notificacion']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir los cambios en caso de rollback
        DB::table('notificaciones')
            ->where('tipo_notificacion', 'tercera_notificacion')
            ->update(['tipo_notificacion' => 'notificacion_carga_vobo']);
    }
}
