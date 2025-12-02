<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'recibo_id','importe','metodo','referencia','pagado_at'
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'pagado_at' => 'datetime',
    ];

    public function recibo()
    {
        return $this->belongsTo(Recibo::class, 'recibo_id');
    }
}
