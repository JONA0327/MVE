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
        Schema::table('manifestation_attachments', function (Blueprint $table) {
            $table->boolean('vucem_compliant')->default(false)->after('mime_type');
            $table->text('conversion_log')->nullable()->after('vucem_compliant');
            $table->json('validation_details')->nullable()->after('conversion_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifestation_attachments', function (Blueprint $table) {
            $table->dropColumn(['vucem_compliant', 'conversion_log', 'validation_details']);
        });
    }
};