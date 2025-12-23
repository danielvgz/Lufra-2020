<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Settings;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $contrato = null;
        $contratoInfo = null;
        if ($user) {
            try {
                $isEmpleado = $user->tieneRol('empleado') || $user->tieneRol('Empleado');
            } catch (\Throwable $e) {
                $isEmpleado = false;
            }

            if ($isEmpleado) {
                $contrato = DB::table('contratos')
                    ->where('empleado_id', $user->id)
                    ->where(function($q) {
                        $q->where('estado', 'activo')
                          ->orWhereNull('fecha_fin')
                          ->orWhereDate('fecha_fin', '>=', now());
                    })
                    ->orderByDesc('id')
                    ->first();

                if ($contrato) {
                    try {
                        $fechaFin = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin) : null;
                        $hoy = \Carbon\Carbon::today();
                        if ($fechaFin) {
                            if ($fechaFin->lt($hoy)) {
                                $daysRemaining = 0;
                                $expired = true;
                            } else {
                                $daysRemaining = $hoy->diffInDays($fechaFin);
                                $expired = false;
                            }
                        } else {
                            $daysRemaining = null; // indefinido
                            $expired = false;
                        }
                    } catch (\Throwable $e) {
                        $daysRemaining = null;
                        $expired = false;
                    }

                    $contratoInfo = [
                        'id' => $contrato->id,
                        'tipo_contrato' => $contrato->tipo_contrato ?? null,
                        'puesto' => $contrato->puesto ?? null,
                        'fecha_inicio' => $contrato->fecha_inicio ?? null,
                        'fecha_fin' => $contrato->fecha_fin ?? null,
                        'days_remaining' => $daysRemaining,
                        'expired' => $expired,
                        'salario_base' => $contrato->salario_base ?? null,
                        'estado' => $contrato->estado ?? null,
                    ];
                }
            }
        }

        return view('perfil', compact('contratoInfo'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'cedula' => ['nullable', 'string', 'max:64'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'talla_ropa' => ['nullable', 'string', 'max:16'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe superar 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico no debe superar 255 caracteres.',
            'email.unique' => 'El correo electrónico ya existe.',
            'current_password.string' => 'La contraseña actual debe ser texto.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => now(),
        ];

        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'] ?? '', $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es válida'])->withInput();
            }
            $update['password'] = Hash::make($data['password']);
        }

        DB::table('users')->where('id', $user->id)->update($update);

        // Guardar preferencia de mostrar/ocultar notificaciones por usuario
        $showNotifications = $request->has('show_notifications') ? '1' : '0';
        Settings::updateOrCreate(
            ['key' => 'user_' . $user->id . '_show_notifications'],
            ['value' => $showNotifications]
        );

        // Actualizar/crear datos de empleado asociados
        try {
            $employeeData = [
                'cedula' => $request->input('cedula'),
                'direccion' => $request->input('direccion'),
                'talla_ropa' => $request->input('talla_ropa'),
            ];
            \App\Models\Employee::updateOrCreate(
                ['user_id' => $user->id],
                $employeeData
            );
        } catch (\Throwable $e) {
            // ignore if empleados table not present or other error
        }
        return redirect()->route('perfil')->with('status', 'Perfil actualizado');
    }

    public function desactivar()
    {
        $uid = auth()->id();
        DB::table('empleados')->where('user_id', $uid)->update([
            'estado' => 'Inactivo',
            'fecha_baja' => now()->toDateString(),
            'updated_at' => now(),
        ]);
        auth()->logout();
        return redirect()->route('login');
    }
}
