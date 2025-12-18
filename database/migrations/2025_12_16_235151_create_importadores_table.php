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
        // Crear tabla de importadores
        Schema::create('importadores', function (Blueprint $table) {
            $table->id();
            $table->string('rfc', 13)->unique();
            $table->string('razon_social');
            $table->string('registro_nacional_contribuyentes')->nullable();
            $table->text('domicilio_fiscal')->nullable();
            $table->timestamps();
            
            $table->index('rfc');
        });

        // Modificar tabla manifestations para usar importador_id
        Schema::table('manifestations', function (Blueprint $table) {
            // Agregar columna importador_id
            $table->foreignId('importador_id')->nullable()->after('id')->constrained('importadores')->onDelete('restrict');
            
            // Los campos antiguos (rfc_importador, razon_social_importador, etc.) 
            // se mantienen temporalmente para migración de datos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestations', function (Blueprint $table) {
            $table->dropForeign(['importador_id']);
            $table->dropColumn('importador_id');
        });
        
        Schema::dropIfExists('importadores');
    }
};
