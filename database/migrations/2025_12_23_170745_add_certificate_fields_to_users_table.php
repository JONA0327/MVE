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
            // Campos para almacenar certificados FIEL cifrados
            $table->longText('fiel_certificate')->nullable()->after('webservice_key'); // Certificado .cer en base64 cifrado
            $table->longText('fiel_private_key')->nullable()->after('fiel_certificate'); // Llave privada .key en base64 cifrado
            $table->text('fiel_password')->nullable()->after('fiel_private_key'); // Contraseña de la llave cifrada
            $table->timestamp('fiel_uploaded_at')->nullable()->after('fiel_password'); // Fecha de subida
            $table->boolean('use_system_certificates')->default(false)->after('fiel_uploaded_at'); // Si usa certificados del sistema o manuales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fiel_certificate',
                'fiel_private_key', 
                'fiel_password',
                'fiel_uploaded_at',
                'use_system_certificates'
            ]);
        });
    }
};
