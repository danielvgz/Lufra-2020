<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaPerfilController extends Controller
{
    public function show()
    {
        // Carga datos guardados (si existen)
        $data = [];
        if (Storage::exists('empresa_perfil.json')) {
            $json = Storage::get('empresa_perfil.json');
            $data = json_decode($json, true) ?: [];
        }
        return view('empresa_perfil', ['perfil' => $data]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['nullable','string','max:255'],
            'ruc' => ['nullable','string','max:255'],
            'correo' => ['nullable','email','max:255'],
            'telefono' => ['nullable','string','max:255'],
            'direccion' => ['nullable','string','max:500'],
            'logo' => ['nullable','image','max:2048'], // 2MB
        ], [
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 255 caracteres.',
            'ruc.max' => 'El identificador fiscal no debe superar 255 caracteres.',
            'correo.email' => 'Debe proporcionar un correo válido.',
            'correo.max' => 'El correo no debe superar 255 caracteres.',
            'telefono.max' => 'El teléfono no debe superar 255 caracteres.',
            'direccion.max' => 'La dirección no debe superar 500 caracteres.',
            'logo.image' => 'El logo debe ser una imagen (PNG/JPG).',
            'logo.max' => 'El logo no debe superar 2MB.',
        ]);

        // Guardar logo si se envió
        $logoPath = null;
        if ($request->hasFile('logo')) {
            // Guarda bajo storage/app/public/empresa_logo.png
            $ext = $request->file('logo')->getClientOriginalExtension();
            $filename = 'empresa_logo.'.strtolower($ext ?: 'png');
            $logoPath = $request->file('logo')->storeAs('public', $filename);
            $validated['logo_path'] = $logoPath; // e.g., public/empresa_logo.png
        }

        // Guardar perfil como JSON en storage/app/empresa_perfil.json
        Storage::put('empresa_perfil.json', json_encode($validated, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

        return redirect()->route('empresa.perfil')->with('status', 'Perfil actualizado correctamente');
    }
}
