<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('authorizations', function (Blueprint $table) {
            // Renombrar columnas existentes
            $table->renameColumn('nombre_responsable', 'name');
            $table->renameColumn('email', 'guard_name');

            // Eliminar la columna grupo_id
            $table->dropColumn('grupo_id');

            // Opcional: Eliminar o modificar 'puesto' si no lo necesitas
            $table->dropColumn('puesto');
        });
    }

    public function down()
    {
        Schema::table('authorizations', function (Blueprint $table) {
            // Revertir los cambios en caso de rollback
            $table->renameColumn('name', 'nombre_responsable');
            $table->renameColumn('guard_name', 'email');

            // Restaurar la columna grupo_id
            $table->integer('grupo_id')->nullable();

            // Restaurar la columna puesto
            $table->string('puesto')->nullable();
        });
    }
};

