<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivosTable extends Migration
{
    public function up()
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisito_id');
            $table->string('evidencia', 10);
            $table->date('fecha_limite_cumplimiento');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 255);
            $table->dateTime('fecha_subida');
            $table->timestamps();

            $table->foreign('requisito_id')->references('id')->on('requisitos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos');
    }
}
