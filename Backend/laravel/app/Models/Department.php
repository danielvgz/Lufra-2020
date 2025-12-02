<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // Usar la tabla 'departamentos' existente
    protected $table = 'departamentos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'parent_id',
        'active',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
