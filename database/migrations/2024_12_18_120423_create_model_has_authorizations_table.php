<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('model_has_authorizations', function (Blueprint $table) {
            // Claves foráneas y columnas
            $table->unsignedBigInteger('authorization_id');
            $table->string('model_type'); // Tipo de modelo
            $table->unsignedBigInteger('model_id'); // ID del modelo

            // Definir la relación con la tabla 'authorizations'
            $table->foreign('authorization_id')
                  ->references('id')
                  ->on('authorizations')
                  ->onDelete('cascade');

            // Índices para optimizar consultas
            $table->index(['model_id', 'model_type'], 'model_has_authorizations_model_type_model_id_index');

            // Opcional: Si es necesario
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_authorizations');
    }
};

