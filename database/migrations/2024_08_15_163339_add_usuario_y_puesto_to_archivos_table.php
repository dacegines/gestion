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
        Schema::table('archivos', function (Blueprint $table) {
            $table->string('usuario')->nullable();
            $table->string('puesto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->dropColumn('usuario');
            $table->dropColumn('puesto');
        });
    }
};
