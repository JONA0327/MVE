<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar tipo_cambio de decimal(10,6) a decimal(16,3) según especificación VUCEM
        Schema::table('manifestation_adjustments', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 16, 3)->default(1)->change();
        });

        // Agregar límite de 20 caracteres a numero_pedimento según especificación VUCEM
        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            $table->string('numero_pedimento', 20)->change();
        });

        // Agregar límite de 20 caracteres a patente según especificación VUCEM
        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            $table->string('patente', 20)->nullable()->change();
        });

        // Agregar límite de 20 caracteres a aduana según especificación VUCEM
        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            $table->string('aduana_clave', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestation_adjustments', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 10, 6)->default(1)->change();
        });

        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            $table->string('numero_pedimento', 255)->change();
            $table->string('patente', 4)->nullable()->change();
            $table->string('aduana_clave', 3)->nullable()->change();
        });
    }
};
