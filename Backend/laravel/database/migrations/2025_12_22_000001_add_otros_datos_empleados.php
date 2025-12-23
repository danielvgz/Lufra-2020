<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'cedula')) {
                $table->string('cedula')->nullable()->after('identificador_fiscal');
            }
            if (!Schema::hasColumn('empleados', 'talla_ropa')) {
                $table->string('talla_ropa')->nullable()->after('direccion');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'talla_ropa')) {
                $table->dropColumn('talla_ropa');
            }
            if (Schema::hasColumn('empleados', 'cedula')) {
                $table->dropColumn('cedula');
            }
        });
    }
};
