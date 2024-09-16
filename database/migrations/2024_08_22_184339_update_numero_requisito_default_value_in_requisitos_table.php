<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('requisitos', function (Blueprint $table) {
            // Modificar la columna numero_requisito para agregar un valor por defecto
            $table->string('numero_requisito')->default('')->change();
        });
    }
    
    public function down()
    {
        Schema::table('requisitos', function (Blueprint $table) {
            // Revertir el cambio, eliminando el valor por defecto
            $table->string('numero_requisito')->default(null)->change();
        });
    }
};
