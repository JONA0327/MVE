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
        Schema::table('consultation_rfcs', function (Blueprint $table) {
            $table->string('tipo_figura')->nullable()->after('rfc_consulta');
            $table->string('nombre')->nullable()->after('tipo_figura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultation_rfcs', function (Blueprint $table) {
            $table->dropColumn(['tipo_figura', 'nombre']);
        });
    }
};
