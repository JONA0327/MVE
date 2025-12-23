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
        // 1. Tabla Principal
        Schema::create('manifestations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Datos del Solicitante (Corregido: Separado por Nombre y Apellidos)
            $table->string('curp_solicitante', 18);
            $table->string('rfc_solicitante', 13);
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');

            // Datos del Importador (Manifestación)
            $table->string('rfc_importador', 13);
            $table->string('razon_social_importador');
            $table->string('registro_nacional_contribuyentes')->nullable();
            
            // Totales (Se actualizan en el Paso 2)
            $table->decimal('total_precio_pagado', 18, 2)->default(0);
            $table->decimal('total_incrementables', 18, 2)->default(0);
            $table->decimal('total_decrementables', 18, 2)->default(0);
            $table->decimal('total_valor_aduana', 18, 2)->default(0);
            $table->decimal('total_precio_por_pagar', 18, 2)->default(0);

            // Datos del Paso 3 (Detalles)
            $table->boolean('existe_vinculacion')->default(false);
            $table->text('descripcion_vinculacion')->nullable();
            $table->string('metodo_valoracion_global')->nullable();
            $table->string('incoterm', 3)->nullable();

            // Estatus y Firma
            $table->string('status')->default('draft'); // draft, signed, submitted
            $table->text('cadena_original')->nullable();
            $table->text('sello_digital')->nullable();
            
            // Rutas para guardar los archivos que regresa el SAT (No generados por nosotros)
            $table->string('path_acuse_manifestacion')->nullable();
            $table->string('path_detalle_manifestacion')->nullable();
            
            $table->timestamps();
        });

        // 2. COVEs (Acuses de Valor)
        Schema::create('manifestation_coves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->string('edocument');
            $table->string('metodo_valoracion')->nullable();
            $table->string('numero_factura');
            $table->date('fecha_expedicion')->nullable();
            $table->string('emisor')->nullable();
            $table->string('destinatario')->nullable();
            $table->timestamps();
        });

        // 3. Pedimentos
        Schema::create('manifestation_pedimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->string('numero_pedimento');
            $table->string('patente', 4)->nullable();
            $table->string('aduana_clave', 3)->nullable();
            $table->timestamps();
        });

        // 4. Ajustes (Incrementables y Decrementables)
        Schema::create('manifestation_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->enum('type', ['incrementable', 'decrementable']); 
            $table->string('concepto');
            $table->date('fecha_erogacion')->nullable();
            $table->decimal('importe', 18, 2)->default(0);
            $table->string('moneda', 3)->default('USD');
            $table->decimal('tipo_cambio', 10, 6)->default(1);
            $table->boolean('a_cargo_importador')->default(false);
            $table->timestamps();
        });

        // 5. Pagos (Precio Pagado y Por Pagar)
        Schema::create('manifestation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->string('status'); // 'paid' o 'payable'
            $table->date('fecha')->nullable();
            $table->decimal('importe', 18, 2)->default(0);
            $table->string('forma_pago')->nullable();
            $table->string('moneda', 3)->default('USD');
            $table->string('situacion_pago')->nullable(); // Solo para 'payable'
            $table->timestamps();
        });
        
        // 6. Compensaciones
        Schema::create('manifestation_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->date('fecha')->nullable();
            $table->string('forma_pago')->nullable();
            $table->text('motivo')->nullable();
            $table->text('prestacion_mercancia')->nullable();
            $table->timestamps();
        });

        // 7. Anexos (Archivos)
        Schema::create('manifestation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->string('tipo_documento');
            $table->string('descripcion_complementaria')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->timestamps();
        });
        
        // 8. RFCs de Consulta (Opcional)
        Schema::create('consultation_rfcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifestation_id')->constrained('manifestations')->onDelete('cascade');
            $table->string('rfc_consulta', 13);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_rfcs');
        Schema::dropIfExists('manifestation_attachments');
        Schema::dropIfExists('manifestation_compensations');
        Schema::dropIfExists('manifestation_payments');
        Schema::dropIfExists('manifestation_adjustments');
        Schema::dropIfExists('manifestation_pedimentos');
        Schema::dropIfExists('manifestation_coves');
        Schema::dropIfExists('manifestations');
    }
};