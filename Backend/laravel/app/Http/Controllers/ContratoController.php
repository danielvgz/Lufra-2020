<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContratoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Verificar rol
        $role = DB::table('rol_usuario')
            ->join('roles', 'roles.id', '=', 'rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');

        if ($role !== 'administrador') {
            abort(403);
        }

        // Construir query con filtros (empleado es ahora un usuario con rol 'empleado')
        $q = DB::table('contratos as c')
            ->join('users as e', 'e.id', '=', 'c.empleado_id')
            ->select('c.*', 'e.name as empleado_name', 'e.email as empleado_email');

        // Búsqueda por texto
        if ($t = trim($request->input('q', ''))) {
            $q->where(function($w) use ($t) {
                $w->where('e.name', 'like', '%'.$t.'%')
                  ->orWhere('e.email', 'like', '%'.$t.'%')
                  ->orWhere('c.puesto', 'like', '%'.$t.'%');
                if (is_numeric($t)) {
                    $w->orWhere('c.empleado_id', '=', $t);
                }
            });
        }
        
        // Aplicar filtros adicionales
        if ($tipo = $request->input('tipo')) {
            $q->where('c.tipo_contrato', $tipo);
        }
        if ($desde = $request->input('desde')) {
            $q->whereDate('c.fecha_inicio', '>=', $desde);
        }
        if ($hasta = $request->input('hasta')) {
            $q->whereDate('c.fecha_fin', '<=', $hasta);
        }

        $items = $q->orderByDesc('c.id')->paginate(20);

        // Alertas de contratos próximos a vencer
        $limite = Carbon::now()->addDays(30)->toDateString();
        $alertas = DB::table('contratos as c')
            ->join('users as e', 'e.id', '=', 'c.empleado_id')
            ->whereNotNull('c.fecha_fin')
            ->whereDate('c.fecha_fin', '<=', $limite)
            ->select('c.id', 'c.fecha_fin', 'e.name as empleado_name')
            ->orderBy('c.fecha_fin', 'asc')
            ->limit(50)
            ->get();

        // Empleados para select
        // Obtener usuarios que tengan el rol 'empleado'
        $emps = DB::table('users')
            ->join('rol_usuario as ru', 'ru.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'ru.rol_id')
            ->whereRaw('lower(roles.nombre) = ?', ['empleado'])
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->limit(200)
            ->get();

        return view('contratos', compact('items', 'alertas', 'emps'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:users,id'],
            'tipo_contrato' => ['nullable', 'string', 'max:64'],
            'frecuencia_pago' => ['nullable', 'string', 'max:64'],
            'puesto' => ['nullable', 'string', 'max:200'],
            'fecha_inicio' => ['nullable', 'date'],
            'periodo_prueba_fin' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'salario_base' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', 'string', 'max:32'],
        ], [
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.integer' => 'El empleado debe ser un número.',
            'empleado_id.exists' => 'El empleado no existe.',
            'tipo_contrato.string' => 'El tipo de contrato debe ser texto.',
            'tipo_contrato.max' => 'El tipo de contrato no debe superar 64 caracteres.',
            'frecuencia_pago.string' => 'La frecuencia de pago debe ser texto.',
            'frecuencia_pago.max' => 'La frecuencia de pago no debe superar 64 caracteres.',
            'puesto.string' => 'El puesto debe ser texto.',
            'puesto.max' => 'El puesto no debe superar 200 caracteres.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'periodo_prueba_fin.date' => 'El periodo de prueba fin debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha fin debe ser una fecha válida.',
            'salario_base.numeric' => 'El salario base debe ser un número.',
            'salario_base.min' => 'El salario base debe ser mayor o igual a 0.',
            'estado.string' => 'El estado debe ser texto.',
            'estado.max' => 'El estado no debe superar 32 caracteres.',
        ]);

        $contratoId = DB::table('contratos')->insertGetId(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        // Obtener nombre del empleado para notificación
        $empleado = DB::table('users')->find($data['empleado_id']);
        $empleadoNombre = $empleado ? ($empleado->name ?? $empleado->email) : 'Empleado';

        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyContratoCreado($contratoId, $empleadoNombre, auth()->id());

        return redirect()->route('contratos.index')->with('success', 'Contrato creado correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['id' => ['nullable']]);

        $upd = [];
        foreach (['tipo_contrato', 'frecuencia_pago', 'puesto', 'fecha_inicio', 'periodo_prueba_fin', 'fecha_fin', 'salario_base', 'estado'] as $field) {
            if ($request->has($field)) {
                $upd[$field] = $request->input($field);
            }
        }

        if (!empty($upd)) {
            $upd['updated_at'] = now();
            DB::table('contratos')->where('id', $id)->update($upd);

            // Obtener nombre del empleado
            $contrato = DB::table('contratos as c')
                ->join('users as e', 'e.id', '=', 'c.empleado_id')
                ->where('c.id', $id)
                ->select('e.name')
                ->first();

            if ($contrato) {
                $empleadoNombre = $contrato->name;
                \App\Http\Controllers\NotificationHelper::notifyContratoEditado($id, $empleadoNombre, auth()->id());
            }
        }

        return redirect()->route('contratos.index')->with('success', 'Contrato actualizado correctamente');
    }

    public function destroy($id)
    {
        // Obtener nombre antes de eliminar
        $contrato = DB::table('contratos as c')
            ->join('users as e', 'e.id', '=', 'c.empleado_id')
            ->where('c.id', $id)
            ->select('e.name')
            ->first();

        DB::table('contratos')->where('id', $id)->delete();

        if ($contrato) {
            $empleadoNombre = "{$contrato->nombre} {$contrato->apellido}";
            \App\Http\Controllers\NotificationHelper::notifyContratoEliminado($empleadoNombre, auth()->id());
        }

        return redirect()->route('contratos.index')->with('success', 'Contrato eliminado correctamente');
    }
}
