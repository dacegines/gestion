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
        if (!Schema::hasTable('roles')) { // Verificar si la tabla existe
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasTable('roles')) { // Verificar si la tabla existe antes de eliminarla
            Schema::dropIfExists('roles');
        }
    }
};
