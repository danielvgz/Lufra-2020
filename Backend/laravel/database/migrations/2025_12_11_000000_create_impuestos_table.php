<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 50)->unique();
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('por_defecto')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuestos');
    }
};
