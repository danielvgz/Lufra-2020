<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('contratos')) {
            Schema::table('contratos', function (Blueprint $table) {
                if (!Schema::hasColumn('contratos', 'puesto')) {
                    $table->string('puesto', 100)->nullable()->after('puesto_id');
                }
                if (!Schema::hasColumn('contratos', 'estado')) {
                    $table->string('estado', 32)->default('activo')->after('salario_base');
                }
                if (!Schema::hasColumn('contratos', 'periodo_prueba_fin')) {
                    $table->date('periodo_prueba_fin')->nullable()->after('fecha_inicio');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contratos')) {
            Schema::table('contratos', function (Blueprint $table) {
                if (Schema::hasColumn('contratos', 'periodo_prueba_fin')) {
                    $table->dropColumn('periodo_prueba_fin');
                }
                if (Schema::hasColumn('contratos', 'estado')) {
                    $table->dropColumn('estado');
                }
                if (Schema::hasColumn('contratos', 'puesto')) {
                    $table->dropColumn('puesto');
                }
            });
        }
    }
};