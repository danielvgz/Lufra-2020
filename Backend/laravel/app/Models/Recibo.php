<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    use HasFactory;

    protected $table = 'recibos';

    protected $fillable = [
        'periodo_nomina_id','empleado_id','bruto','devengado','impuesto_monto','impuesto_id','deducciones','detalle_deducciones','neto','estado','locked_at'
    ];

    protected $casts = [
        'detalle_deducciones' => 'array',
        'locked_at' => 'datetime',
        'bruto' => 'decimal:2',
        'devengado' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'deducciones' => 'decimal:2',
        'neto' => 'decimal:2',
    ];

    public function empleado()
    {
        return $this->belongsTo(Employee::class, 'empleado_id');
    }

    public function periodo()
    {
        return $this->belongsTo(PayrollPeriod::class, 'periodo_nomina_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'recibo_id');
    }
}
