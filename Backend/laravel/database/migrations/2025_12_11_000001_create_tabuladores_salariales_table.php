<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabuladores_salariales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('cargo', 100)->nullable();
            $table->enum('frecuencia', ['semanal', 'quincenal', 'mensual']);
            $table->decimal('sueldo_base', 12, 2);
            $table->string('moneda', 10)->default('VES');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabuladores_salariales');
    }
};
