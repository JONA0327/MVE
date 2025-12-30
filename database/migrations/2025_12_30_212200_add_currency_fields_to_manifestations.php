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
            // Agregar campos de moneda para cada campo de valor
            $table->string('moneda_precio_pagado', 3)->default('USD')->after('total_precio_pagado');
            $table->string('moneda_incrementables', 3)->default('USD')->after('total_incrementables');
            $table->string('moneda_decrementables', 3)->default('USD')->after('total_decrementables');
            $table->string('moneda_precio_por_pagar', 3)->default('USD')->after('total_precio_por_pagar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestations', function (Blueprint $table) {
            $table->dropColumn([
                'moneda_precio_pagado',
                'moneda_incrementables',
                'moneda_decrementables',
                'moneda_precio_por_pagar'
            ]);
        });
    }
};
