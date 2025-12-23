<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'numero_empleado',
        'nombre',
        'apellido',
        'correo',
        'identificador_fiscal',
        'fecha_nacimiento',
        'fecha_ingreso',
        'fecha_baja',
        'estado',
        'telefono',
        'direccion',
        'banco',
        'cuenta_bancaria',
        'notas',
        // vínculo a usuario con rol empleado
        'user_id',
        // nuevos campos
        'department_id',
        'puesto',
        'cedula',
        'talla_ropa',
        'salario_base',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_baja' => 'date',
        'salario_base' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
