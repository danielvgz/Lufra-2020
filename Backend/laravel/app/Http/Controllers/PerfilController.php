<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('perfil');
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
