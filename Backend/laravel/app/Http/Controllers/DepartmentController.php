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
        $query = Department::query()->with(['parent', 'children']);

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->integer('parent_id'));
        }



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
            'parent_id' => ['nullable', 'integer', 'exists:departamentos,id'],
        ]);


        $department = Department::create($data);

        return response()->json($department->load(['parent', 'children']), 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department->load(['parent', 'children']));
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'codigo' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('departamentos', 'codigo')->ignore($department->id)],
            'descripcion' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:departamentos,id'],
        ]);

        if (isset($data['parent_id']) && (int)$data['parent_id'] === (int)$department->id) {
            return response()->json(['message' => 'parent_id cannot be the same as the department id'], 422);
        }

        $department->update($data);

        return response()->json($department->load(['parent', 'children']));
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(null, 204);
    }
}
