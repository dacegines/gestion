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
        Schema::table('responsables', function (Blueprint $table) {
            $table->integer('grupo_id')->nullable()->after('email'); // Nueva columna para agrupar registros
        });
    }
    
    public function down()
    {
        Schema::table('responsables', function (Blueprint $table) {
            $table->dropColumn('grupo_id');
        });
    }
    
};
