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
        // ===== MODIFICACIONES A TABLA USERS =====
        Schema::table('users', function (Blueprint $table) {
            // RFC ya existe, solo agregamos razón social y demás campos
            $table->string('razon_social')->nullable()->after('rfc');
            $table->text('actividad_economica')->nullable()->after('razon_social');
            
            // Domicilio fiscal del solicitante
            $table->string('pais', 100)->nullable()->after('actividad_economica');
            $table->string('codigo_postal', 10)->nullable()->after('pais');
            $table->string('estado', 100)->nullable()->after('codigo_postal');
            $table->string('municipio', 100)->nullable()->after('estado');
            $table->string('localidad', 100)->nullable()->after('municipio');
            $table->string('colonia', 100)->nullable()->after('localidad');
            $table->string('calle', 255)->nullable()->after('colonia');
            $table->string('numero_exterior', 20)->nullable()->after('calle');
            $table->string('numero_interior', 20)->nullable()->after('numero_exterior');
            
            // Datos de contacto
            $table->string('lada', 5)->nullable()->after('numero_interior');
            $table->string('telefono', 20)->nullable()->after('lada');
            
            // Flag para indicar si el perfil está completo
            $table->boolean('profile_completed')->default(false)->after('telefono');
        });

        // ===== MODIFICACIONES A TABLA MANIFESTATIONS =====
        Schema::table('manifestations', function (Blueprint $table) {
            // 1. Eliminar campos antiguos del solicitante
            $table->dropColumn(['curp_solicitante', 'rfc_solicitante', 'nombre', 'apellido_paterno', 'apellido_materno']);
            
            // 2. Agregar campos nuevos del solicitante
            $table->string('rfc_solicitante', 13)->after('uuid');
            $table->string('razon_social_solicitante')->after('rfc_solicitante');
            $table->text('actividad_economica_solicitante')->after('razon_social_solicitante');
            
            // Domicilio fiscal del solicitante
            $table->string('pais_solicitante', 100)->after('actividad_economica_solicitante');
            $table->string('codigo_postal_solicitante', 10)->after('pais_solicitante');
            $table->string('estado_solicitante', 100)->after('codigo_postal_solicitante');
            $table->string('municipio_solicitante', 100)->after('estado_solicitante');
            $table->string('localidad_solicitante', 100)->nullable()->after('municipio_solicitante');
            $table->string('colonia_solicitante', 100)->after('localidad_solicitante');
            $table->string('calle_solicitante', 255)->after('colonia_solicitante');
            $table->string('numero_exterior_solicitante', 20)->after('calle_solicitante');
            $table->string('numero_interior_solicitante', 20)->nullable()->after('numero_exterior_solicitante');
            
            // Datos de contacto del solicitante
            $table->string('lada_solicitante', 5)->after('numero_interior_solicitante');
            $table->string('telefono_solicitante', 20)->after('lada_solicitante');
            $table->string('correo_solicitante')->after('telefono_solicitante');
            
            // 3. Agregar campos EME
            $table->text('domicilio_fiscal_importador')->nullable()->after('razon_social_importador');
            $table->date('fecha_factura')->nullable()->after('domicilio_fiscal_importador');
            $table->date('fecha_entrada')->nullable()->after('fecha_factura');
            $table->date('fecha_pago_pedimento')->nullable()->after('fecha_entrada');
            $table->date('fecha_presentacion')->nullable()->after('fecha_pago_pedimento');
            $table->text('observaciones_pedimento')->nullable()->after('fecha_presentacion');
            $table->enum('data_source', ['manual', 'eme'])->default('manual')->after('observaciones_pedimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ===== REVERTIR TABLA USERS =====
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'razon_social',
                'actividad_economica',
                'pais',
                'codigo_postal',
                'estado',
                'municipio',
                'localidad',
                'colonia',
                'calle',
                'numero_exterior',
                'numero_interior',
                'lada',
                'telefono',
                'profile_completed'
            ]);
        });

        // ===== REVERTIR TABLA MANIFESTATIONS =====
        Schema::table('manifestations', function (Blueprint $table) {
            // Eliminar campos nuevos
            $table->dropColumn([
                'rfc_solicitante',
                'razon_social_solicitante',
                'actividad_economica_solicitante',
                'pais_solicitante',
                'codigo_postal_solicitante',
                'estado_solicitante',
                'municipio_solicitante',
                'localidad_solicitante',
                'colonia_solicitante',
                'calle_solicitante',
                'numero_exterior_solicitante',
                'numero_interior_solicitante',
                'lada_solicitante',
                'telefono_solicitante',
                'correo_solicitante',
                'domicilio_fiscal_importador',
                'fecha_factura',
                'fecha_entrada',
                'fecha_pago_pedimento',
                'fecha_presentacion',
                'observaciones_pedimento',
                'data_source'
            ]);
            
            // Restaurar campos antiguos
            $table->string('curp_solicitante', 18)->after('uuid');
            $table->string('rfc_solicitante', 13)->after('curp_solicitante');
            $table->string('nombre')->after('rfc_solicitante');
            $table->string('apellido_paterno')->after('nombre');
            $table->string('apellido_materno')->after('apellido_paterno');
        });
    }
};
