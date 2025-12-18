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
        Schema::table('manifestations', function (Blueprint $table) {
            // Expandir campos del solicitante para almacenar datos encriptados
            // Los datos encriptados con Laravel Crypt necesitan más espacio que los originales
            $table->text('rfc_solicitante')->change();
            $table->text('razon_social_solicitante')->change();
            $table->text('actividad_economica_solicitante')->change();
            $table->text('pais_solicitante')->change();
            $table->text('codigo_postal_solicitante')->change();
            $table->text('estado_solicitante')->change();
            $table->text('municipio_solicitante')->change();
            $table->text('localidad_solicitante')->change();
            $table->text('colonia_solicitante')->change();
            $table->text('calle_solicitante')->change();
            $table->text('numero_exterior_solicitante')->change();
            $table->text('numero_interior_solicitante')->change();
            $table->text('lada_solicitante')->change();
            $table->text('telefono_solicitante')->change();
            $table->text('correo_solicitante')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestations', function (Blueprint $table) {
            // Revertir a los tipos de datos originales (esto puede causar pérdida de datos)
            $table->string('rfc_solicitante', 13)->change();
            $table->string('razon_social_solicitante')->change();
            $table->string('actividad_economica_solicitante')->change();
            $table->string('pais_solicitante')->change();
            $table->string('codigo_postal_solicitante')->change();
            $table->string('estado_solicitante')->change();
            $table->string('municipio_solicitante')->change();
            $table->string('localidad_solicitante')->change();
            $table->string('colonia_solicitante')->change();
            $table->string('calle_solicitante')->change();
            $table->string('numero_exterior_solicitante')->change();
            $table->string('numero_interior_solicitante')->change();
            $table->string('lada_solicitante')->change();
            $table->string('telefono_solicitante')->change();
            $table->string('correo_solicitante')->change();
        });
    }
};
