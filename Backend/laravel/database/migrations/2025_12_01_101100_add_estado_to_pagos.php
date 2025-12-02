<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            Schema::table('pagos', function (Blueprint $table) {
                if (!Schema::hasColumn('pagos', 'estado')) {
                    $table->string('estado', 20)->default('pendiente');
                }
                if (!Schema::hasColumn('pagos', 'respondido_en')) {
                    $table->timestamp('respondido_en')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pagos')) {
            Schema::table('pagos', function (Blueprint $table) {
                if (Schema::hasColumn('pagos', 'respondido_en')) {
                    $table->dropColumn('respondido_en');
                }
                if (Schema::hasColumn('pagos', 'estado')) {
                    $table->dropColumn('estado');
                }
            });
        }
    }
};
