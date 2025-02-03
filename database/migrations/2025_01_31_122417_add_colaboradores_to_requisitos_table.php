<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColaboradoresToRequisitosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisitos', function (Blueprint $table) {
            $table->string('colaborador1', 100)->nullable()->after('email'); // Columna después de email
            $table->string('colaborador2', 100)->nullable()->after('colaborador1'); // Columna después de colaborador1
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisitos', function (Blueprint $table) {
            $table->dropColumn(['colaborador1', 'colaborador2']); // Eliminar ambas columnas
        });
    }
}

