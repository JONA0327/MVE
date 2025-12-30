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
        Schema::table('manifestation_payments', function (Blueprint $table) {
            // Agregar tipo_cambio para pagos (decimal 16,3 según VUCEM)
            $table->decimal('tipo_cambio', 16, 3)->default(1)->after('moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestation_payments', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });
    }
};
