<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacionsTable extends Migration
{
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisito_id');
            $table->string('numero_evidencia', 50);
            $table->string('id_notificacion', 50);
            $table->string('nombre', 255);
            $table->string('email', 255);
            $table->string('tipo_notificacion', 50);
            $table->timestamps();

            $table->foreign('requisito_id')->references('id')->on('requisitos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}
