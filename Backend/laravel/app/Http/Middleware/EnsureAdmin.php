<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * Allow if user role is 'admin' (either on user model or via rol_usuario join)
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $role = $user->role ?? null;

        if (!$role) {
            // attempt to query rol_usuario
            try {
                $role = DB::table('rol_usuario')
                    ->join('roles','roles.id','=','rol_usuario.rol_id')
                    ->where('rol_usuario.user_id', $user->id)
                    ->value('roles.nombre');
            } catch (\Throwable $e) {
                $role = null;
            }
        }

        if ($role) {
            $r = strtolower($role);
            $allowed = ['admin', 'administrator', 'administrador', 'superadmin', 'owner'];
            foreach ($allowed as $a) {
                if (strpos($r, $a) !== false) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Acceso denegado. Se requieren privilegios de administrador.');
    }
}
