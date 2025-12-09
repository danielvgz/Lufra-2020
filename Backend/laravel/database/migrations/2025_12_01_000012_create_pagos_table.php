<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recibo_id')->constrained('recibos')->cascadeOnDelete();
                $table->decimal('importe', 12, 2);
                $table->string('moneda', 10)->nullable();
                $table->string('metodo', 50);
                $table->string('referencia', 100)->nullable();
                $table->string('estado', 20)->default('pendiente');
                $table->timestamp('pagado_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
