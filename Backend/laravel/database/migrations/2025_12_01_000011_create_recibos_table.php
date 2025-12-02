<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('recibos')) {
            Schema::create('recibos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periodo_id')->constrained('periodos_nomina')->cascadeOnDelete();
                $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
                $table->decimal('bruto', 12, 2)->default(0);
                $table->decimal('deducciones', 12, 2)->default(0);
                $table->json('detalle_deducciones')->nullable();
                $table->decimal('neto', 12, 2)->default(0);
                $table->string('estado', 20)->default('calculado');
                $table->timestamp('locked_at')->nullable();
                $table->timestamps();
                $table->unique(['periodo_id','empleado_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
