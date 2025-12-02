<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('periodos_nomina')) {
            Schema::create('periodos_nomina', function (Blueprint $table) {
                $table->id();
                $table->string('codigo')->unique();
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->string('estado', 20)->default('abierto');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_nomina');
    }
};
