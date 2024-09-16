<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitosTable extends Migration
{
    public function up()
    {
        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('numero_requisito');
            $table->string('requisito', 255);
            $table->string('sub_requisito', 255)->nullable();
            $table->string('periodicidad', 50);
            $table->string('numero_evidencia', 10);
            $table->text('evidencia');
            $table->integer('porcentaje');
            $table->float('avance');
            $table->date('fecha_limite_cumplimiento');
            $table->string('responsable', 100);
            $table->string('origen_obligacion', 255);
            $table->text('clausula_condicionante_articulo')->nullable();
            $table->string('id_notificaciones', 10);
            $table->integer('approved');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisitos');
    }
}
