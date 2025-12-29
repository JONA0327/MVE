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
        // Agregar campo "especifique" a pagos (70 caracteres según especificación VUCEM)
        Schema::table('manifestation_payments', function (Blueprint $table) {
            $table->string('especifique', 70)->nullable()->after('forma_pago');
        });

        // Agregar campo "especifique" a compensaciones (70 caracteres según especificación VUCEM)
        Schema::table('manifestation_compensations', function (Blueprint $table) {
            $table->string('especifique', 70)->nullable()->after('forma_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestation_payments', function (Blueprint $table) {
            $table->dropColumn('especifique');
        });

        Schema::table('manifestation_compensations', function (Blueprint $table) {
            $table->dropColumn('especifique');
        });
    }
};
