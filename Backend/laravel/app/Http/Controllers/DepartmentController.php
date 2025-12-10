<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::query();

        // Búsqueda por nombre o código: admite ?q= o ?search=
        $term = $request->input('q', $request->input('search'));
        if (is_string($term) && $term !== '') {
            $like = '%' . str_replace(['%','_'], ['\%','\_'], $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nombre', 'like', $like)
                  ->orWhere('codigo', 'like', $like);
            });
        }

        return response()->json($query->orderBy('nombre')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('departamentos', 'codigo')],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 255 caracteres.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no debe superar 50 caracteres.',
            'codigo.unique' => 'El código ya existe.',
            'descripcion.string' => 'La descripción debe ser texto.',
        ]);

        $department = Department::create($data);

        return response()->json($department, 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'codigo' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('departamentos', 'codigo')->ignore($department->id)],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 255 caracteres.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no debe superar 50 caracteres.',
            'codigo.unique' => 'El código ya existe.',
            'descripcion.string' => 'La descripción debe ser texto.',
        ]);

        $department->update($data);

        return response()->json($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(null, 204);
    }
}
