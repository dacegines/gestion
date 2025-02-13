<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('obligacion_usuario', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental

            // Relación con users (user_id → id)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Si se elimina el usuario, también sus registros en esta tabla

            // Número de requisito (relación con otra tabla si aplica)
            $table->bigInteger('numero_requisito')->unsigned();

            // Cambiamos "view" por "view"
            $table->tinyInteger('view')->default(1);

            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('obligacion_usuario');
    }
};
