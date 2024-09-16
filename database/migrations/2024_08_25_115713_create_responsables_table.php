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
        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_responsable')->nullable();
            $table->string('puesto')->nullable();  // Nueva columna para el puesto
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsables');
    }
};
