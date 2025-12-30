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
        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            // Aumentar numero_pedimento a 30 caracteres para soportar formato con espacios
            // Formato: YY AAA PPPP NNNNNNN (ejemplo: 25 480 3429 5001084)
            $table->string('numero_pedimento', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestation_pedimentos', function (Blueprint $table) {
            $table->string('numero_pedimento', 20)->change();
        });
    }
};
