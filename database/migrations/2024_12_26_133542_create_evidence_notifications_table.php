<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evidence_notifications', function (Blueprint $table) {
            $table->id(); // ID nunca es nulo (clave primaria)
            $table->string('position')->nullable(); // Permite valores nulos
            $table->string('email')->nullable(); // Permite valores nulos
            $table->integer('type')->nullable(); // Permite valores nulos
            $table->timestamps(); // timestamps también permite nulls en algunos casos (ver explicación abajo)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_notifications');
    }
};