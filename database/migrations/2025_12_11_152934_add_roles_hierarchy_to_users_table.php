<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reemplazamos la lógica simple de is_admin por roles
            $table->enum('role', ['super_admin', 'admin', 'operator'])->default('operator')->after('email');
            
            // Jerarquía: Quién creó a este usuario (El Admin es padre del Operador)
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['role', 'parent_id']);
        });
    }
};