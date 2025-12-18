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
        Schema::table('users', function (Blueprint $table) {
            // Expandir campos para permitir datos encriptados (más largos)
            $table->text('rfc')->nullable()->change();
            $table->text('razon_social')->nullable()->change();
            $table->text('actividad_economica')->nullable()->change();
            $table->text('pais')->nullable()->change();
            $table->text('codigo_postal')->nullable()->change();
            $table->text('estado')->nullable()->change();
            $table->text('municipio')->nullable()->change();
            $table->text('localidad')->nullable()->change();
            $table->text('colonia')->nullable()->change();
            $table->text('calle')->nullable()->change();
            $table->text('numero_exterior')->nullable()->change();
            $table->text('numero_interior')->nullable()->change();
            $table->text('lada')->nullable()->change();
            $table->text('telefono')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir a los tipos originales
            $table->string('rfc', 13)->nullable()->change();
            $table->string('razon_social')->nullable()->change();
            $table->string('actividad_economica')->nullable()->change();
            $table->string('pais')->nullable()->change();
            $table->string('codigo_postal', 10)->nullable()->change();
            $table->string('estado')->nullable()->change();
            $table->string('municipio')->nullable()->change();
            $table->string('localidad')->nullable()->change();
            $table->string('colonia')->nullable()->change();
            $table->string('calle')->nullable()->change();
            $table->string('numero_exterior', 10)->nullable()->change();
            $table->string('numero_interior', 10)->nullable()->change();
            $table->string('lada', 5)->nullable()->change();
            $table->string('telefono', 15)->nullable()->change();
        });
    }
};
