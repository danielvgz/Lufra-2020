<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('empleados')) {
            Schema::create('empleados', function (Blueprint $table) {
                $table->id();
                $table->string('numero_empleado', 50)->nullable();
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('correo', 255);
                $table->string('identificador_fiscal', 50)->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->date('fecha_ingreso');
                $table->date('fecha_baja')->nullable();
                $table->string('estado', 20)->default('activo');
                $table->string('telefono', 50)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->string('banco', 100)->nullable();
                $table->string('cuenta_bancaria', 100)->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('empleados', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments');
            }
            if (!Schema::hasColumn('empleados', 'puesto')) {
                $table->string('puesto', 100)->nullable();
            }
            if (!Schema::hasColumn('empleados', 'salario_base')) {
                $table->decimal('salario_base', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) {
                if (Schema::hasColumn('empleados', 'salario_base')) {
                    $table->dropColumn('salario_base');
                }
                if (Schema::hasColumn('empleados', 'puesto')) {
                    $table->dropColumn('puesto');
                }
                if (Schema::hasColumn('empleados', 'department_id')) {
                    $table->dropConstrainedForeignId('department_id');
                }
                if (Schema::hasColumn('empleados', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }
            });
        }
    }
};
