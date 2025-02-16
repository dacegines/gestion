<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('obligacion_usuario', function (Blueprint $table) {
            // Renombrar la columna de numero_requisito a numero_evidencia
            $table->renameColumn('numero_requisito', 'numero_evidencia');

            // Cambiar su tipo de dato a VARCHAR(50)
            $table->string('numero_evidencia', 50)->change();
        });
    }

    public function down()
    {
        Schema::table('obligacion_usuario', function (Blueprint $table) {
            // Revertir el nombre a numero_requisito
            $table->renameColumn('numero_evidencia', 'numero_requisito');

            // Revertir el tipo de dato a BIGINT UNSIGNED
            $table->bigInteger('numero_requisito')->unsigned()->change();
        });
    }
};
