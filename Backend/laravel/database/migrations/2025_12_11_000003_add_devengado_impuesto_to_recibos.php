<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->decimal('devengado', 12, 2)->default(0)->after('bruto');
            $table->decimal('impuesto_monto', 12, 2)->default(0)->after('deducciones');
            $table->unsignedBigInteger('impuesto_id')->nullable()->after('impuesto_monto');
            $table->foreign('impuesto_id')->references('id')->on('impuestos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign(['impuesto_id']);
            $table->dropColumn(['devengado', 'impuesto_monto', 'impuesto_id']);
        });
    }
};
