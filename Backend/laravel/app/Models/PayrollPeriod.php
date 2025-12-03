<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $table = 'periodos_nomina';

    protected $fillable = [
        'codigo', 'fecha_inicio', 'fecha_fin', 'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'periodo_nomina_id');
    }
}
