<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 32)->unique()->nullable();
            $table->string('nombre', 200);
            $table->string('descripcion', 1000)->nullable();
            $table->unsignedBigInteger('id_responsable')->nullable();
            $table->timestamps();
        });

        Schema::create('puestos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->string('titulo', 200);
            $table->string('descripcion', 1000)->nullable();
            $table->timestamps();
            $table->foreign('departamento_id')->references('id')->on('departamentos')->nullOnDelete();
        });

        Schema::create('empleados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_empleado', 64)->unique()->nullable();
            $table->string('nombre', 150);
            $table->string('apellido', 150);
            $table->string('correo', 250)->unique()->nullable();
            $table->string('identificador_fiscal', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('estado', 32)->default('activo');
            $table->string('telefono', 50)->nullable();
            $table->string('direccion', 500)->nullable();
            $table->string('banco', 200)->nullable();
            $table->string('cuenta_bancaria', 200)->nullable();
            $table->string('notas', 2000)->nullable();
            $table->timestamps();
            $table->index('numero_empleado');
            $table->index('correo');
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empleado_id');
            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('tipo_contrato', 32);
            $table->string('frecuencia_pago', 32);
            $table->decimal('salario_base', 14, 2);
            $table->string('moneda_pago', 8)->default('EUR');
            $table->decimal('horas_por_semana', 6, 2)->nullable();
            $table->timestamps();
            $table->foreign('empleado_id')->references('id')->on('empleados')->cascadeOnDelete();
            $table->foreign('puesto_id')->references('id')->on('puestos')->nullOnDelete();
        });

        Schema::create('periodos_nomina', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 64)->unique()->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 32)->default('abierto');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->index(['fecha_inicio', 'fecha_fin']);
        });

        Schema::create('componentes_salario', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 64)->unique()->nullable();
            $table->string('nombre', 200);
            $table->string('tipo', 32);
            $table->string('calculo', 32)->default('fijo');
            $table->decimal('valor', 14, 4)->default(0.0);
            $table->boolean('gravable')->default(true);
            $table->boolean('visible_en_recibo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('recibos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empleado_id');
            $table->unsignedBigInteger('contrato_id')->nullable();
            $table->unsignedBigInteger('periodo_nomina_id');
            $table->decimal('bruto', 14, 2)->default(0.00);
            $table->decimal('total_percepciones', 14, 2)->default(0.00);
            $table->decimal('total_deducciones', 14, 2)->default(0.00);
            $table->decimal('deducciones', 14, 2)->default(0.00);
            $table->json('detalle_deducciones')->nullable();
            $table->decimal('neto', 14, 2)->default(0.00);
            $table->string('estado', 32)->default('borrador');
            $table->timestamp('emitido_en')->nullable();
            $table->timestamp('pagado_en')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->foreign('empleado_id')->references('id')->on('empleados')->cascadeOnDelete();
            $table->foreign('contrato_id')->references('id')->on('contratos')->nullOnDelete();
            $table->foreign('periodo_nomina_id')->references('id')->on('periodos_nomina')->cascadeOnDelete();
            $table->index(['empleado_id', 'periodo_nomina_id'], 'idx_recibos_empleado_periodo');
        });

        Schema::create('lineas_recibo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('recibo_id');
            $table->unsignedBigInteger('componente_id')->nullable();
            $table->string('descripcion', 500)->nullable();
            $table->decimal('importe', 14, 2);
            $table->decimal('porcentaje', 10, 4)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('recibo_id')->references('id')->on('recibos')->cascadeOnDelete();
            $table->foreign('componente_id')->references('id')->on('componentes_salario')->nullOnDelete();
        });

        Schema::create('tasas_impuesto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 200);
            $table->decimal('porcentaje', 6, 4);
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('contribuciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 200);
            $table->decimal('tasa_empleado', 6, 4)->default(0.0);
            $table->decimal('tasa_empleador', 6, 4)->default(0.0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('recibo_id');
            $table->string('metodo', 64);
            $table->decimal('importe', 14, 2);
            $table->string('moneda', 10)->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('pagado_en')->useCurrent();
            $table->string('referencia', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('recibo_id')->references('id')->on('recibos')->cascadeOnDelete();
        });

        Schema::create('registro_auditoria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('usuario_identificador', 200)->nullable();
            $table->string('accion', 100);
            $table->string('tabla', 200)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->text('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_auditoria');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('lineas_recibo');
        Schema::dropIfExists('recibos');
        Schema::dropIfExists('componentes_salario');
        Schema::dropIfExists('periodos_nomina');
        Schema::dropIfExists('contribuciones');
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('puestos');
        Schema::dropIfExists('departamentos');
    }
};
