<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $table = 'themes';

    protected $fillable = ['name','slug','installed_at','active','meta'];

    protected $casts = [
        'installed_at' => 'datetime',
        'active' => 'boolean',
        'meta' => 'array'
    ];
}
