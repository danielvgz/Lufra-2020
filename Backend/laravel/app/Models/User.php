<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // roles del usuario
    public function roles()
    {
        return DB::table('rol_usuario')->where('user_id', $this->id)->pluck('rol_id');
    }

    public function tieneRol(string $nombre): bool
    {
        $rolId = DB::table('roles')->where('nombre', $nombre)->value('id');
        if (!$rolId) return false;
        return DB::table('rol_usuario')->where(['user_id' => $this->id, 'rol_id' => $rolId])->exists();
    }

    public function puede(string $permiso): bool
    {
        $permisoId = DB::table('permisos')->where('nombre', $permiso)->value('id');
        if (!$permisoId) return false;
        return DB::table('rol_usuario as ru')
            ->join('permiso_rol as pr', 'ru.rol_id', '=', 'pr.rol_id')
            ->where('ru.user_id', $this->id)
            ->where('pr.permiso_id', $permisoId)
            ->exists();
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('read', false);
    }
}
