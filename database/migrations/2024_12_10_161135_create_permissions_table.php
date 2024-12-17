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
        if (!Schema::hasTable('permissions')) { // Verifica si la tabla ya existe
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasTable('permissions')) { // Verifica si la tabla existe antes de borrarla
            Schema::dropIfExists('permissions');
        }
    }
    
};
