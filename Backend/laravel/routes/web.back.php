<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmpresaPerfilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inicio', [HomeController::class, 'index'])->name('inicio');


// Authentication routes
Route::get('/registro', [RegisterController::class, 'show'])->name('register');
Route::post('/registro', [RegisterController::class, 'register'])->name('register.post');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/home', [DashboardController::class, 'index'])->middleware('auth')->name('home');

// Configuración y empresa
Route::middleware('auth')->group(function () {
    Route::view('/roles', 'roles')->name('roles.index');
    Route::view('/permissions', 'permissions')->name('permissions.index');

    // Perfil de usuario
    Route::get('/perfil', function(){ return view('perfil'); })->name('perfil');
    Route::post('/perfil', function(\Illuminate\Http\Request $request){
        $user = auth()->user();
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', \Illuminate\Validation\Rule::unique('users','email')->ignore($user->id)],
            'current_password' => ['nullable','string'],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);
        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => now(),
        ];
        if (!empty($data['password'])) {
            // Verify current password before changing
            if (!\Illuminate\Support\Facades\Hash::check($data['current_password'] ?? '', $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es válida'])->withInput();
            }
            $update['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        \Illuminate\Support\Facades\DB::table('users')->where('id',$user->id)->update($update);
        return redirect()->route('perfil')->with('status','Perfil actualizado');
    })->name('perfil.update');
    Route::post('/perfil/desactivar', function(){
        $uid = auth()->id();
        \Illuminate\Support\Facades\DB::table('empleados')->where('user_id',$uid)->update([
            'estado' => 'Inactivo',
            'fecha_baja' => now()->toDateString(),
            'updated_at' => now(),
        ]);
        auth()->logout();
        return redirect()->route('login');
    })->name('perfil.desactivar');

    // Nóminas (periodos)
    Route::get('/nominas', [\App\Http\Controllers\NominaController::class, 'index'])->name('nominas.index');
    Route::get('/recibos-pagos', [\App\Http\Controllers\RecibosPagosController::class, 'index'])->name('recibos_pagos');

    // CRUD Conceptos de pago
    Route::get('/conceptos', function(){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        
        // Insertar conceptos por defecto si no existen
        $count = \Illuminate\Support\Facades\DB::table('conceptos_pago')->count();
        if ($count === 0) {
            \Illuminate\Support\Facades\DB::table('conceptos_pago')->insert([
                ['nombre' => 'Nómina', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Bono', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Anticipo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Vacaciones', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        return view('conceptos');
    })->name('conceptos.view');
    Route::post('/conceptos/crear', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['nombre' => ['required','string','max:100']]);
        \Illuminate\Support\Facades\DB::table('conceptos_pago')->updateOrInsert(['nombre'=>$data['nombre']], ['created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('conceptos.view')->with('success','Concepto guardado');
    })->name('conceptos.crear');
    Route::post('/conceptos/editar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer'],'nombre' => ['required','string','max:100']]);
        \Illuminate\Support\Facades\DB::table('conceptos_pago')->where('id',$data['id'])->update(['nombre'=>$data['nombre'], 'updated_at'=>now()]);
        return redirect()->route('conceptos.view')->with('success','Concepto actualizado');
    })->name('conceptos.editar');
    Route::post('/conceptos/eliminar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer']]);
        \Illuminate\Support\Facades\DB::table('conceptos_pago')->where('id',$data['id'])->delete();
        return redirect()->route('conceptos.view')->with('success','Concepto eliminado');
    })->name('conceptos.eliminar');

    // CRUD Métodos de pago
    Route::get('/metodos', function(){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        
        // Insertar métodos por defecto si no existen
        $count = \Illuminate\Support\Facades\DB::table('metodos_pago')->count();
        if ($count === 0) {
            \Illuminate\Support\Facades\DB::table('metodos_pago')->insert([
                ['nombre' => 'Transferencia', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Efectivo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Pago móvil', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        return view('metodos');
    })->name('metodos.view');
    Route::post('/metodos/crear', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['nombre' => ['required','string','max:50']]);
        \Illuminate\Support\Facades\DB::table('metodos_pago')->updateOrInsert(['nombre'=>$data['nombre']], ['created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('metodos.view')->with('success','Método guardado');
    })->name('metodos.crear');
    Route::post('/metodos/editar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer'],'nombre' => ['required','string','max:50']]);
        \Illuminate\Support\Facades\DB::table('metodos_pago')->where('id',$data['id'])->update(['nombre'=>$data['nombre'], 'updated_at'=>now()]);
        return redirect()->route('metodos.view')->with('success','Método actualizado');
    })->name('metodos.editar');
    Route::post('/metodos/eliminar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer']]);
        \Illuminate\Support\Facades\DB::table('metodos_pago')->where('id',$data['id'])->delete();
        return redirect()->route('metodos.view')->with('success','Método eliminado');
    })->name('metodos.eliminar');

    // CRUD Monedas
    Route::get('/monedas', function(){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        
        // Insertar monedas por defecto si no existen
        $count = \Illuminate\Support\Facades\DB::table('monedas')->count();
        if ($count === 0) {
            \Illuminate\Support\Facades\DB::table('monedas')->insert([
                ['nombre' => 'Bolívar', 'codigo' => 'VES', 'simbolo' => 'Bs.', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Dólar Estadounidense', 'codigo' => 'USD', 'simbolo' => '$', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        return view('monedas');
    })->name('monedas.view');
    Route::post('/monedas/crear', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['nombre' => ['required','string','max:50'],'codigo' => ['required','string','max:3'],'simbolo' => ['nullable','string','max:10']]);
        \Illuminate\Support\Facades\DB::table('monedas')->updateOrInsert(['nombre'=>$data['nombre'],'codigo'=>$data['codigo']], ['simbolo'=>$data['simbolo'],'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('monedas.view')->with('success','Moneda guardada');
    })->name('monedas.crear');
    Route::post('/monedas/editar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer'],'nombre' => ['required','string','max:50'],'codigo' => ['required','string','max:3'],'simbolo' => ['nullable','string','max:10']]);
        \Illuminate\Support\Facades\DB::table('monedas')->where('id',$data['id'])->update(['nombre'=>$data['nombre'],'codigo'=>$data['codigo'],'simbolo'=>$data['simbolo'], 'updated_at'=>now()]);
        return redirect()->route('monedas.view')->with('success','Moneda actualizada');
    })->name('monedas.editar');
    Route::post('/monedas/eliminar', function(\Illuminate\Http\Request $request){
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        $data = $request->validate(['id'=>['required','integer']]);
        \Illuminate\Support\Facades\DB::table('monedas')->where('id',$data['id'])->delete();
        return redirect()->route('monedas.view')->with('success','Moneda eliminada');
    })->name('monedas.eliminar');

    Route::get('/contratos', [\App\Http\Controllers\ContratoController::class, 'index'])->name('contratos.index');
    Route::post('/contratos', [\App\Http\Controllers\ContratoController::class, 'store'])->name('contratos.store');
    Route::post('/contratos/{id}', [\App\Http\Controllers\ContratoController::class, 'update'])->name('contratos.update');
    Route::post('/contratos/{id}/delete', [\App\Http\Controllers\ContratoController::class, 'destroy'])->name('contratos.destroy');

    // Legacy routes - redirigir al controlador
    /*Route::post('/contratos', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'empleado_id' => ['required','integer','exists:empleados,id'],
            'puesto' => ['required','string','max:100'],
            'salario_base' => ['required','numeric','min:0'],
            'estado' => ['required','in:activo,suspendido,terminado'],
            'tipo_contrato' => ['nullable','string','max:50'],
            'frecuencia_pago' => ['required','in:mensual,quincenal,semanal'],
            'fecha_inicio' => ['required','date'],
            'periodo_prueba_fin' => ['nullable','date'],
            'fecha_fin' => ['nullable','date'],
        ]);
        $insert = [
            'empleado_id' => $data['empleado_id'],
            'puesto' => $data['puesto'],
            'salario_base' => $data['salario_base'],
            'estado' => $data['estado'],
            'frecuencia_pago' => $data['frecuencia_pago'],
            'fecha_inicio' => $data['fecha_inicio'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
        foreach (['tipo_contrato','periodo_prueba_fin','fecha_fin'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') { $insert[$k] = $data[$k]; }
        }
        $contratoId = \Illuminate\Support\Facades\DB::table('contratos')->insertGetId($insert);
        
        // Obtener nombre del empleado
        $empleado = \Illuminate\Support\Facades\DB::table('empleados')->where('id', $data['empleado_id'])->first();
        $empleadoNombre = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido) : 'Empleado';
        
        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyContratoCreado($contratoId, $empleadoNombre, auth()->id());
        
        return redirect()->route('contratos.index')->with('success','Contrato creado correctamente');
    })->name('contratos.store');
    Route::post('/contratos/{id}', function(\Illuminate\Http\Request $request, $id){
        $request->validate(['id' => ['nullable']]);
        $update = [];
        $map = [
            'puesto' => ['string','max:100'],
            'salario_base' => ['numeric','min:0'],
            'estado' => ['in:activo,suspendido,terminado'],
            'tipo_contrato' => ['string','max:50'],
            'frecuencia_pago' => ['in:mensual,quincenal,semanal'],
            'fecha_inicio' => ['date'],
            'periodo_prueba_fin' => ['date'],
            'fecha_fin' => ['date'],
        ];
        foreach ($map as $field => $rules) {
            if ($request->has($field)) {
                $request->validate([$field => array_merge(['nullable'], $rules)]);
                $val = $request->input($field);
                if ($val !== null && $val !== '') { $update[$field] = $val; }
            }
        }
        if (!empty($update)) {
            $update['updated_at'] = now();
            \Illuminate\Support\Facades\DB::table('contratos')->where('id', $id)->update($update);
            
            // Obtener nombre del empleado
            $contrato = \Illuminate\Support\Facades\DB::table('contratos')->where('id', $id)->first();
            if ($contrato) {
                $empleado = \Illuminate\Support\Facades\DB::table('empleados')->where('id', $contrato->empleado_id)->first();
                $empleadoNombre = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido) : 'Empleado';
                
                // Notificar a otros administradores
                \App\Http\Controllers\NotificationHelper::notifyContratoEditado($id, $empleadoNombre, auth()->id());
            }
        }
        return redirect()->route('contratos.index')->with('success','Contrato actualizado correctamente');
    })->name('contratos.update');
    Route::post('/contratos/{id}/delete', function($id){
        // Obtener información del contrato antes de eliminarlo
        $contrato = \Illuminate\Support\Facades\DB::table('contratos')->where('id', $id)->first();
        $empleadoNombre = 'Empleado';
        
        if ($contrato) {
            $empleado = \Illuminate\Support\Facades\DB::table('empleados')->where('id', $contrato->empleado_id)->first();
            $empleadoNombre = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido) : 'Empleado';
        }
        
        \Illuminate\Support\Facades\DB::table('contratos')->where('id',$id)->delete();
        
        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyContratoEliminado($empleadoNombre, auth()->id());
        
        return redirect()->route('contratos.index')->with('success','Contrato eliminado correctamente');
    })->name('contratos.destroy');
    Route::get('/recibos-pagos/reportes', function(\Illuminate\Http\Request $request){
        $desde = $request->date('desde');
        $hasta = $request->date('hasta');
        $q = \Illuminate\Support\Facades\DB::table('recibos as r')
            ->join('periodos_nomina as p','p.id','=','r.periodo_nomina_id')
            ->select('p.id','p.codigo','p.fecha_inicio','p.fecha_fin', \Illuminate\Support\Facades\DB::raw('COUNT(r.id) as recibos'), \Illuminate\Support\Facades\DB::raw('SUM(r.neto) as total_neto'))
            ->groupBy('p.id','p.codigo','p.fecha_inicio','p.fecha_fin')
            ->orderByDesc('p.fecha_inicio');
        if ($desde) { $q->whereDate('p.fecha_inicio','>=',$desde); }
        if ($hasta) { $q->whereDate('p.fecha_fin','<=',$hasta); }
        $periodos = $q->limit(100)->get();
        return view('recibos_pagos_reportes', ['periodos'=>$periodos, 'desde'=>$desde, 'hasta'=>$hasta]);
    })->name('recibos_pagos.reportes');

    // Reporte detallado imprimible (PDF via navegador)
    Route::get('/recibos-pagos/reportes/detalle', function(\Illuminate\Http\Request $request){
        $desde = $request->date('desde');
        $hasta = $request->date('hasta');
        $q = \Illuminate\Support\Facades\DB::table('recibos as r')
            ->join('periodos_nomina as p','p.id','=','r.periodo_nomina_id')
            ->join('empleados as e','e.id','=','r.empleado_id')
            ->leftJoin('pagos as pg','pg.recibo_id','=','r.id')
            ->select('p.codigo as periodo','p.fecha_inicio','p.fecha_fin','r.id as recibo_id','e.nombre','e.apellido','r.neto','pg.id as pago_id','pg.metodo','pg.importe','pg.estado','pg.referencia as descripcion')
            ->orderByDesc('p.fecha_inicio')
            ->orderByDesc('r.id');
        if ($desde) { $q->whereDate('p.fecha_inicio','>=',$desde); }
        if ($hasta) { $q->whereDate('p.fecha_fin','<=',$hasta); }
        if ($term = trim($request->input('q',''))) {
            $q->where(function($w) use ($term){
                $w->where('e.nombre','like','%'.$term.'%')
                  ->orWhere('e.apellido','like','%'.$term.'%')
                  ->orWhere('p.codigo','like','%'.$term.'%')
                  ->orWhere('r.id','=',$term);
            });
        }
        $rows = $q->limit(500)->get();
        return view('recibos_pagos_reportes_detalle', ['rows'=>$rows, 'desde'=>$desde, 'hasta'=>$hasta, 'q'=>$request->input('q','')]);
    })->name('recibos_pagos.reportes_detalle');
    Route::post('/nominas/periodo', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'frecuencia' => ['required','in:semanal,quincenal,mensual'],
            'fecha_inicio' => ['required','date'],
        ]);
        $inicio = \Carbon\Carbon::parse($data['fecha_inicio']);
        switch ($data['frecuencia']) {
            case 'semanal':
                $fin = $inicio->copy()->addDays(6);
                break;
            case 'quincenal':
                $fin = $inicio->copy()->addDays(14);
                break;
            default: // mensual
                $fin = $inicio->copy()->endOfMonth();
                break;
        }
        $codigo = strtoupper(substr($data['frecuencia'],0,1)).'-'.$inicio->format('Ymd');
        $existe = \Illuminate\Support\Facades\DB::table('periodos_nomina')->where('codigo',$codigo)->exists();
        \Illuminate\Support\Facades\DB::table('periodos_nomina')->updateOrInsert(
            ['codigo' => $codigo],
            [
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $fin->toDateString(),
                'estado' => 'abierto',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $msg = $existe ? 'El período ya existía, datos actualizados.' : 'Período creado correctamente.';
        return redirect()->route('nominas.index')->with('success', $msg);
    })->name('nominas.periodo.crear');
    Route::post('/nominas/periodo/cerrar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'periodo_id' => ['required','integer','exists:periodos_nomina,id'],
        ]);
        // Cerrar el período
        \Illuminate\Support\Facades\DB::table('periodos_nomina')->where('id',$data['periodo_id'])->update([
            'estado' => 'cerrado',
            'updated_at' => now(),
        ]);
        // Generar recibos para todos los empleados activos que no tengan recibo en este período
        $empleadosActivos = \Illuminate\Support\Facades\DB::table('empleados')->where('estado','activo')->select('id')->get();
        foreach ($empleadosActivos as $emp) {
            $existe = \Illuminate\Support\Facades\DB::table('recibos')
                ->where('empleado_id',$emp->id)
                ->where('periodo_nomina_id',$data['periodo_id'])
                ->exists();
            if (!$existe) {
                // Por ahora, bruto=0, neto=0; se calculará después. Esto habilita "Pagos por asignar".
                $reciboId = \Illuminate\Support\Facades\DB::table('recibos')->insertGetId([
                    'empleado_id' => $emp->id,
                    'periodo_nomina_id' => $data['periodo_id'],
                    'bruto' => 0,
                    // deducciones opcional según esquema
                    'neto' => 0,
                    'estado' => 'aprobado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Notificar al empleado sobre el nuevo recibo
                \App\Http\Controllers\NotificationHelper::notifyReciboCreado($reciboId, $emp->id);
            }
        }
        return redirect()->route('recibos_pagos')->with('success','Período cerrado y recibos generados. Asigne pagos a los empleados del período.');
    })->name('nominas.periodo.cerrar');
    // Nómina y pagos (API JSON)
    Route::prefix('/nomina')->group(function(){
        Route::get('/periodos', [\App\Http\Controllers\PayrollController::class, 'listPeriods'])->name('nomina.periodos');
        Route::post('/calcular', [\App\Http\Controllers\PayrollController::class, 'calculate'])->name('nomina.calcular');
        Route::post('/aprobar', [\App\Http\Controllers\PayrollController::class, 'approve'])->name('nomina.aprobar');
        Route::post('/pagar', [\App\Http\Controllers\PayrollController::class, 'pay'])->name('nomina.pagar');
        Route::get('/recibos/{recibo}/pdf', [\App\Http\Controllers\PayrollController::class, 'receiptPdf'])->name('nomina.recibo.pdf');
        Route::get('/reportes', [\App\Http\Controllers\PayrollController::class, 'reports'])->name('nomina.reportes');
        Route::get('/obligaciones', [\App\Http\Controllers\PayrollController::class, 'obligations'])->name('nomina.obligaciones');
        Route::get('/periodos/{periodo}/banco', [\App\Http\Controllers\PayrollController::class, 'bankFile'])->name('nomina.banco');
    });

    // Departamentos (vista y CRUD) - solo admin
    Route::get('/departamentos', function(){ 
        $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles','roles.id','=','rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');
        if ($role !== 'administrador') { abort(403); }
        return view('departamentos'); 
    })->name('departamentos.view');
    Route::post('/departamentos/nuevo', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'code' => ['required','string','max:50', \Illuminate\Validation\Rule::unique('departamentos','codigo')],
            'description' => ['nullable','string'],
            'parent_id' => ['nullable','integer','exists:departamentos,id'],
            ]);
        $deptoId = DB::table('departamentos')->insertGetId([
            'nombre'=>$data['name'], 'codigo'=>$data['code'], 'descripcion'=>$data['description'] ?? null,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        
        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyDepartamentoCreado($deptoId, $data['name'], auth()->id());
        
        return redirect()->route('departamentos.view')->with('success','Departamento creado correctamente');
    })->name('departamentos.nuevo');
    Route::post('/departamentos/editar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'id' => ['required','integer'],
            'name' => ['required','string','max:255'],
            'code' => ['required','string','max:50', \Illuminate\Validation\Rule::unique('departamentos','codigo')->ignore($request->id)],
            'description' => ['nullable','string'],
            'parent_id' => ['nullable','integer','exists:departamentos,id'],
            ]);
        DB::table('departamentos')->where('id',$data['id'])->update([
            'nombre'=>$data['name'], 'codigo'=>$data['code'], 'descripcion'=>$data['description'] ?? null,
            'updated_at'=>now()
        ]);
        
        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyDepartamentoEditado($data['id'], $data['name'], auth()->id());
        
        return redirect()->route('departamentos.view')->with('success','Departamento actualizado correctamente');
    })->name('departamentos.editar');
    Route::post('/departamentos/eliminar', function(\Illuminate\Http\Request $request){
        $data = $request->validate(['id' => ['required','integer']]);
        
        // Obtener nombre del departamento antes de eliminarlo
        $departamento = DB::table('departamentos')->where('id',$data['id'])->first();
        $nombreDepto = $departamento ? $departamento->nombre : 'Departamento';
        
        DB::table('departamentos')->where('id',$data['id'])->delete();
        
        // Notificar a otros administradores
        \App\Http\Controllers\NotificationHelper::notifyDepartamentoEliminado($nombreDepto, auth()->id());
        
        return redirect()->route('departamentos.view')->with('success','Departamento eliminado correctamente');
    })->name('departamentos.eliminar');

    // Departamentos (JSON API)
    Route::prefix('/departamentos/api')->group(function(){
        Route::get('/', [DepartmentController::class, 'index'])->name('departamentos.index');
        Route::post('/', [DepartmentController::class, 'store'])->name('departamentos.store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('departamentos.show');
        Route::match(['put','patch'], '/{department}', [DepartmentController::class, 'update'])->name('departamentos.update');
        // Eliminación opcional si se requiere más adelante:
        // Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departamentos.destroy');
    });

    // Empleados (API JSON)
    Route::prefix('/empleados/api')->group(function(){
        Route::get('/', [EmployeeController::class, 'index'])->name('empleados.api.index');
        Route::post('/', [EmployeeController::class, 'store'])->name('empleados.api.store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('empleados.api.show');
        Route::match(['put','patch'], '/{employee}', [EmployeeController::class, 'update'])->name('empleados.api.update');
        // Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('empleados.api.destroy');
    });

    // Empleados (vista: lista usuarios con rol empleado) - solo admin
    Route::get('/empleados', [\App\Http\Controllers\EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/detalle/{userId}', [\App\Http\Controllers\EmpleadoController::class, 'detalle'])->name('empleados.detalle');

    // Migrar empleados (tabla empleados) a usuarios (tabla users) y asignar rol "empleado"
    Route::post('/empleados/migrar', function(\Illuminate\Http\Request $request){
        $empleados = DB::table('empleados')->get();
        $rolEmpleadoId = DB::table('roles')->where('nombre','empleado')->value('id');
        foreach ($empleados as $e) {
            $exists = DB::table('users')->where('email', $e->correo)->exists();
            if (!$exists) {
                $uid = DB::table('users')->insertGetId([
                    'name' => trim(($e->nombre ?? '').' '.($e->apellido ?? '')) ?: ($e->numero_empleado ?? 'Empleado'),
                    'email' => $e->correo,
                    'password' => \Illuminate\Support\Str::password(12),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                if ($rolEmpleadoId) {
                    DB::table('rol_usuario')->updateOrInsert(['user_id'=>$uid,'rol_id'=>$rolEmpleadoId], []);
                }
            }
        }
        return redirect()->route('empleados.index')->with('status','Migración completada: empleados ahora como usuarios.');
    })->name('empleados.migrar');

    // Crear usuario con rol empleado
    Route::post('/empleados/crear', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', \Illuminate\Validation\Rule::unique('users','email')],
            'password' => ['required','confirmed','min:8'],
        ]);
        $uid = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rolEmpleadoId = DB::table('roles')->where('nombre','empleado')->value('id');
        if (!$rolEmpleadoId) {
            $rolEmpleadoId = DB::table('roles')->insertGetId(['nombre'=>'empleado','descripcion'=>null,'created_at'=>now(),'updated_at'=>now()]);
        }
        DB::table('rol_usuario')->updateOrInsert(['user_id'=>$uid,'rol_id'=>$rolEmpleadoId], []);
        return redirect()->route('empleados.index')->with('success','Usuario creado correctamente');
    })->name('empleados.crear');
    Route::post('/empleados/store', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'numero_empleado' => ['required','string','max:50'],
            'nombre' => ['required','string','max:100'],
            'apellido' => ['required','string','max:100'],
            'correo' => ['required','email','max:255'],
            'identificador_fiscal' => ['nullable','string','max:50'],
            'fecha_nacimiento' => ['required','date'],
            'fecha_ingreso' => ['required','date'],
            'fecha_baja' => ['nullable','date'],
            'estado' => ['required','string','max:20'],
            'telefono' => ['nullable','string','max:50'],
            'direccion' => ['nullable','string','max:255'],
            'banco' => ['nullable','string','max:100'],
            'cuenta_bancaria' => ['nullable','string','max:100'],
            'notas' => ['nullable','string','max:500'],
        ]);
        \Illuminate\Support\Facades\DB::table('empleados')->insert([
            'numero_empleado'=>$data['numero_empleado'],
            'nombre'=>$data['nombre'],
            'apellido'=>$data['apellido'],
            'correo'=>$data['correo'],
            'identificador_fiscal'=>$data['identificador_fiscal'] ?? null,
            'fecha_nacimiento'=>$data['fecha_nacimiento'],
            'fecha_ingreso'=>$data['fecha_ingreso'],
            'fecha_baja'=>$data['fecha_baja'] ?? null,
            'estado'=>$data['estado'],
            'telefono'=>$data['telefono'] ?? null,
            'direccion'=>$data['direccion'] ?? null,
            'banco'=>$data['banco'] ?? null,
            'cuenta_bancaria'=>$data['cuenta_bancaria'] ?? null,
            'notas'=>$data['notas'] ?? null,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
        return redirect()->route('empleados.index')->with('success','Empleado creado correctamente');
    })->name('empleados.store');
    Route::post('/empleados/password', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'user_id' => ['required','integer'],
            'password' => ['required','confirmed','min:8'],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
        $user = \App\Models\User::findOrFail($data['user_id']);
        $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        $user->save();
        return redirect()->route('empleados.index')->with('success','Contraseña actualizada correctamente');
    })->name('empleados.password');

    Route::post('/empleados/update', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'id' => ['required','integer'],
            'numero_empleado' => ['required','string','max:50'],
            'nombre' => ['required','string','max:100'],
            'apellido' => ['required','string','max:100'],
            'correo' => ['required','email','max:255'],
            'identificador_fiscal' => ['nullable','string','max:50'],
            'fecha_nacimiento' => ['required','date'],
            'fecha_ingreso' => ['required','date'],
            'fecha_baja' => ['nullable','date'],
            'estado' => ['required','string','max:20'],
            'telefono' => ['nullable','string','max:50'],
            'direccion' => ['nullable','string','max:255'],
            'banco' => ['nullable','string','max:100'],
            'cuenta_bancaria' => ['nullable','string','max:100'],
            'notas' => ['nullable','string','max:500'],
        ]);
        \Illuminate\Support\Facades\DB::table('empleados')->where('id',$data['id'])->update([
            'numero_empleado'=>$data['numero_empleado'],
            'nombre'=>$data['nombre'],
            'apellido'=>$data['apellido'],
            'correo'=>$data['correo'],
            'identificador_fiscal'=>$data['identificador_fiscal'] ?? null,
            'fecha_nacimiento'=>$data['fecha_nacimiento'],
            'fecha_ingreso'=>$data['fecha_ingreso'],
            'fecha_baja'=>$data['fecha_baja'] ?? null,
            'estado'=>$data['estado'],
            'telefono'=>$data['telefono'] ?? null,
            'direccion'=>$data['direccion'] ?? null,
            'banco'=>$data['banco'] ?? null,
            'cuenta_bancaria'=>$data['cuenta_bancaria'] ?? null,
            'notas'=>$data['notas'] ?? null,
            'updated_at'=>now(),
        ]);
        return redirect()->route('empleados.index')->with('success','Empleado actualizado correctamente');
    })->name('empleados.update');

    Route::post('/empleados/destroy', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'id' => ['required','integer'],
        ]);
        \Illuminate\Support\Facades\DB::table('empleados')->where('id',$data['id'])->delete();
        return redirect()->route('empleados.index')->with('success','Empleado eliminado correctamente');
    })->name('empleados.destroy');
    Route::get('/empleados/detalle/{id}', function($id){
        $u = \Illuminate\Support\Facades\DB::table('users')->select('id','name','email')->find($id);
        return view('empleados', ['detalle' => $u]);
    })->name('empleados.detalle');

    Route::post('/empleados/editar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'user_id' => ['required','integer'],
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', \Illuminate\Validation\Rule::unique('users','email')->ignore($request->user_id)],
        ]);
        $user = \App\Models\User::findOrFail($data['user_id']);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();
        return redirect()->route('empleados.index')->with('success','Usuario actualizado correctamente');
    })->name('empleados.editar');

    Route::post('/empleados/asignar-departamento', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'user_id' => ['required','integer'],
            'department_id' => ['required','integer'],
        ]);
        // Mapear departamento 'departamentos' -> 'departments' para cumplir FK
        $depSrc = \Illuminate\Support\Facades\DB::table('departamentos')->where('id',$data['department_id'])->first();
        if (!$depSrc) { return redirect()->route('empleados.index')->with('error','Departamento no encontrado'); }
        $depIdFk = \Illuminate\Support\Facades\DB::table('departments')->where('code',$depSrc->codigo)->value('id');
        if (!$depIdFk) {
            $depIdFk = \Illuminate\Support\Facades\DB::table('departments')->insertGetId([
                'name' => $depSrc->nombre,
                'code' => $depSrc->codigo,
                'description' => $depSrc->descripcion ?? null,
                'parent_id' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $emp = \Illuminate\Support\Facades\DB::table('empleados')->where('user_id',$data['user_id'])->first();
        if ($emp) {
            \Illuminate\Support\Facades\DB::table('empleados')->where('user_id',$data['user_id'])->update([
                'department_id' => $depIdFk,
                'updated_at' => now(),
            ]);
        } else {
            $u = \App\Models\User::findOrFail($data['user_id']);
            \Illuminate\Support\Facades\DB::table('empleados')->insert([
                'user_id' => $u->id,
                'numero_empleado' => 'USR-'.$u->id,
                'nombre' => $u->name,
                'apellido' => '-',
                'correo' => $u->email,
                'fecha_nacimiento' => null,
                'fecha_ingreso' => now()->toDateString(),
                'fecha_baja' => null,
                'estado' => 'activo',
                'telefono' => null,
                'direccion' => null,
                'banco' => null,
                'cuenta_bancaria' => null,
                'identificador_fiscal' => null,
                'notas' => null,
                'department_id' => $depIdFk,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return redirect()->route('empleados.index')->with('success','Departamento asignado correctamente');
    })->name('empleados.asignar_departamento');

    Route::post('/empleados/eliminar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'user_id' => ['required','integer'],
        ]);
        $user = \App\Models\User::findOrFail($data['user_id']);
        $user->delete();
        return redirect()->route('empleados.index');
    })->name('empleados.eliminar');

    // Notificaciones
    Route::get('/notificaciones', function(){ return view('notificaciones'); })->name('notificaciones.view');
    Route::get('/notifications/all', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.all');
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifications/delete-read', [\App\Http\Controllers\NotificationController::class, 'deleteRead'])->name('notifications.delete_read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Ruta de prueba para crear notificación directa
    Route::get('/notifications/create-direct', function(){
        try {
            $userId = auth()->id();
            
            // Crear notificación directamente en BD
            $id = DB::table('notifications')->insertGetId([
                'user_id' => $userId,
                'type' => 'departamento_creado',
                'title' => 'Prueba Directa',
                'message' => 'Esta notificación fue creada directamente en la BD a las ' . now()->format('H:i:s'),
                'data' => json_encode(['test' => true, 'departamento_id' => 999]),
                'read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $notification = DB::table('notifications')->where('id', $id)->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación creada directamente en BD',
                'notification_id' => $id,
                'notification' => $notification,
                'check_at' => url('/notificaciones'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    })->name('notifications.create_direct');
    
    // Ruta de prueba para notificaciones de departamentos
    Route::get('/notifications/test-departamento', function(){
        try {
            $userId = auth()->id();
            
            // Simular creación de departamento
            \App\Http\Controllers\NotificationHelper::notifyDepartamentoCreado(999, 'Departamento de Prueba', $userId);
            
            $count = DB::table('notifications')->where('user_id', '!=', $userId)->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación de departamento creada',
                'notifications_created' => $count,
                'note' => 'Las notificaciones fueron enviadas a otros administradores, no a ti mismo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    })->name('notifications.test_departamento');
    
    // Ruta de prueba para crear notificaciones
    Route::get('/notifications/test', function(){
        try {
            // Verificar tabla
            $tableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
            if (empty($tableExists)) {
                return response()->json([
                    'error' => 'La tabla notifications no existe. Ejecutar: php artisan migrate',
                    'solution' => 'Ejecuta: php artisan migrate',
                ], 500);
            }
            
            // Crear notificación de prueba
            $userId = auth()->id();
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'type' => 'test',
                'title' => 'Notificación de Prueba',
                'message' => 'Esta es una notificación de prueba creada en ' . now()->format('Y-m-d H:i:s'),
                'data' => json_encode(['test' => true]),
                'read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $count = DB::table('notifications')->where('user_id', $userId)->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación de prueba creada',
                'total_notifications' => $count,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    })->name('notifications.test');
    
    Route::get('/notifications/debug', function(){
        try {
            $userId = auth()->id();
            
            // Verificar tabla
            $tableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
            
            if (empty($tableExists)) {
                return response()->json([
                    'error' => 'La tabla notifications NO existe',
                    'solution' => 'Ejecuta: php artisan migrate',
                ], 500);
            }
            
            // Obtener notificaciones
            $notifications = DB::table('notifications')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Obtener todas las notificaciones (para debug)
            $allNotifications = DB::table('notifications')->get();
            
            // Verificar usuarios admin
            $admins = DB::table('users')
                ->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')
                ->join('roles', 'roles.id', '=', 'rol_usuario.rol_id')
                ->where('roles.nombre', 'administrador')
                ->select('users.id', 'users.name', 'users.email')
                ->get();
            
            return response()->json([
                'status' => 'OK',
                'user_id' => $userId,
                'user_name' => auth()->user()->name,
                'total_notifications' => $notifications->count(),
                'total_all_notifications' => $allNotifications->count(),
                'notifications' => $notifications,
                'all_notifications' => $allNotifications,
                'administrators' => $admins,
                'test_url' => url('/notifications/test'),
                'test_depto_url' => url('/notifications/test-departamento'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    })->name('notifications.debug');

    // Pagos: aceptación/rechazo por empleado
    Route::post('/pagos/{pago}/aceptar', function(int $pago){
        $uid = auth()->id();
        $pagoData = \Illuminate\Support\Facades\DB::table('pagos as p')
            ->join('recibos as r','r.id','=','p.recibo_id')
            ->join('empleados as e','e.id','=','r.empleado_id')
            ->where('p.id',$pago)
            ->where('e.user_id',$uid)
            ->select('p.id', 'e.id as empleado_id')
            ->first();
        if (!$pagoData) { abort(403); }
        \Illuminate\Support\Facades\DB::table('pagos')->where('id',$pago)->update([
            'estado' => 'aceptado',
            'respondido_en' => now(),
            'updated_at' => now(),
        ]);
        \App\Http\Controllers\NotificationHelper::notifyReciboAceptado($pago, $pagoData->empleado_id);
        return redirect()->back();
    })->name('pagos.aceptar');
    Route::post('/pagos/{pago}/rechazar', function(int $pago){
        $uid = auth()->id();
        $pagoData = \Illuminate\Support\Facades\DB::table('pagos as p')
            ->join('recibos as r','r.id','=','p.recibo_id')
            ->join('empleados as e','e.id','=','r.empleado_id')
            ->where('p.id',$pago)
            ->where('e.user_id',$uid)
            ->select('p.id', 'e.id as empleado_id')
            ->first();
        if (!$pagoData) { abort(403); }
        \Illuminate\Support\Facades\DB::table('pagos')->where('id',$pago)->update([
            'estado' => 'rechazado',
            'respondido_en' => now(),
            'updated_at' => now(),
        ]);
        \App\Http\Controllers\NotificationHelper::notifyReciboRechazado($pago, $pagoData->empleado_id);
        return redirect()->back();
    })->name('pagos.rechazar');

    // Asignar pago a un recibo (admin)
    Route::post('/pagos/asignar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'recibo_id' => ['required','integer','exists:recibos,id'],
            'importe' => ['required','numeric','min:0'],
            'moneda' => ['required','string','max:3'],
            'metodo' => ['required','string','max:50'],
            'concepto' => ['nullable','string','max:100'],
        ]);
        
        $recibo = \Illuminate\Support\Facades\DB::table('recibos')->where('id',$data['recibo_id'])->first();
        
        // upsert pago por recibo
        $exists = \Illuminate\Support\Facades\DB::table('pagos')->where('recibo_id',$data['recibo_id'])->exists();
        if ($exists) {
            \Illuminate\Support\Facades\DB::table('pagos')->where('recibo_id',$data['recibo_id'])->update([
                'importe' => $data['importe'],
                'moneda' => $data['moneda'],
                'metodo' => $data['metodo'],
                'referencia' => $data['concepto'] ?? null,
                'estado' => 'pendiente',
                'updated_at' => now(),
            ]);
        } else {
            \Illuminate\Support\Facades\DB::table('pagos')->insert([
                'recibo_id' => $data['recibo_id'],
                'importe' => $data['importe'],
                'moneda' => $data['moneda'],
                'metodo' => $data['metodo'],
                'referencia' => $data['concepto'] ?? null,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Notificar al empleado sobre el nuevo recibo
            if ($recibo) {
                \App\Http\Controllers\NotificationHelper::notifyReciboCreado($data['recibo_id'], $recibo->empleado_id);
            }
        }
        return redirect()->route('recibos_pagos');
    })->name('pagos.asignar');

    // Crear pago manual (sin período/recibo)
    Route::post('/pagos/manual', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'empleado_id' => ['required','integer','exists:empleados,id'],
            'importe' => ['required','numeric','min:0'],
            'moneda' => ['required','string','max:3'],
            'metodo' => ['required','string','max:50'],
        ]);
        // Crear un recibo ad-hoc para vincular el pago manual
        $periodoId = \Illuminate\Support\Facades\DB::table('periodos_nomina')->orderByDesc('fecha_inicio')->value('id');
        if (!$periodoId) { return redirect()->route('recibos_pagos')->with('error','No existe un período de nómina para vincular el pago manual.'); }
        $reciboId = \Illuminate\Support\Facades\DB::table('recibos')->insertGetId([
            'empleado_id' => $data['empleado_id'],
            'periodo_nomina_id' => $periodoId,
            'bruto' => $data['importe'],
            'neto' => $data['importe'],
            'estado' => 'aprobado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('pagos')->insert([
            'recibo_id' => $reciboId,
            'importe' => $data['importe'],
            'moneda' => $data['moneda'],
            'metodo' => $data['metodo'],
            'estado' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('recibos_pagos');
    })->name('pagos.manual');

    // Empresa perfil
    Route::get('/empresa/perfil', [EmpresaPerfilController::class, 'show'])->name('empresa.perfil');
    Route::post('/empresa/perfil', [EmpresaPerfilController::class, 'update'])->name('empresa.perfil.update');

    // Roles: crear y asignar (simple endpoints)
    Route::post('/roles/nuevo', function(\Illuminate\Http\Request $request){
        $request->validate(['nombre' => ['required','string','max:100']], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.string' => 'El nombre del rol debe ser texto.',
            'nombre.max' => 'El nombre del rol no debe superar 100 caracteres.',
        ]);
        DB::table('roles')->updateOrInsert(['nombre' => $request->nombre], ['descripcion' => null, 'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('roles.index');
    })->name('roles.nuevo');

    Route::post('/roles/asignar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'user_id' => ['required','integer'],
            'roles' => ['array']
        ], [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.integer' => 'El identificador de usuario debe ser un número.',
            'roles.array' => 'Los roles deben enviarse como lista.',
        ]);
        $uid = (int)$data['user_id'];
        DB::table('rol_usuario')->where('user_id',$uid)->delete();
        $roles = $data['roles'] ?? [];
        foreach($roles as $rid){
            DB::table('rol_usuario')->updateOrInsert(['user_id'=>$uid,'rol_id'=>$rid], []);
        }
        return redirect()->route('roles.index');
    })->name('roles.asignar');

    Route::post('/roles/editar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'rol_id' => ['required','integer'],
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ]);
        DB::table('roles')->where('id',$data['rol_id'])->update([
            'nombre'=>$data['nombre'],
            'descripcion'=>$data['descripcion'] ?? null,
            'updated_at'=>now()
        ]);
        return redirect()->route('roles.index');
    })->name('roles.editar');

    Route::post('/roles/eliminar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'rol_id' => ['required','integer'],
        ]);
        $rid = (int)$data['rol_id'];
        DB::table('rol_usuario')->where('rol_id',$rid)->delete();
        DB::table('permiso_rol')->where('rol_id',$rid)->delete();
        DB::table('roles')->where('id',$rid)->delete();
        return redirect()->route('roles.index');
    })->name('roles.eliminar');

    //Configuración del perfil de la empresa

  Route::get('/configuracion', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/configuracion', [SettingController::class, 'store'])->name('settings.store');

    // Permisos: crear y asignar a roles
    Route::post('/permissions/nuevo', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ], [
            'nombre.required' => 'El nombre del permiso es obligatorio.',
            'nombre.string' => 'El nombre del permiso debe ser texto.',
            'nombre.max' => 'El nombre del permiso no debe superar 100 caracteres.',
            'descripcion.max' => 'La descripción es demasiado larga.'
        ]);
        DB::table('permisos')->updateOrInsert(['nombre'=>$data['nombre']], ['descripcion'=>$data['descripcion'] ?? null,'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('permissions.index');
    })->name('permissions.nuevo');

    Route::post('/permissions/asignar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'rol_id' => ['required','integer'],
            'permisos' => ['array']
        ], [
            'rol_id.required' => 'El rol es obligatorio.',
            'rol_id.integer' => 'El identificador de rol debe ser numérico.',
            'permisos.array' => 'Los permisos deben enviarse como lista.'
        ]);
        $rid = (int)$data['rol_id'];
        DB::table('permiso_rol')->where('rol_id',$rid)->delete();
        foreach(($data['permisos'] ?? []) as $pid){
            DB::table('permiso_rol')->updateOrInsert(['rol_id'=>$rid,'permiso_id'=>$pid], []);
        }
        return redirect()->route('permissions.index');
    })->name('permissions.asignar');

    Route::post('/permissions/editar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'permiso_id' => ['required','integer'],
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ]);
        DB::table('permisos')->where('id',$data['permiso_id'])->update([
            'nombre'=>$data['nombre'],
            'descripcion'=>$data['descripcion'] ?? null,
            'updated_at'=>now()
        ]);
        return redirect()->route('permissions.index');
    })->name('permissions.editar');

    Route::post('/permissions/eliminar', function(\Illuminate\Http\Request $request){
        $data = $request->validate([
            'permiso_id' => ['required','integer'],
        ]);
        $pid = (int)$data['permiso_id'];
        DB::table('permiso_rol')->where('permiso_id',$pid)->delete();
        DB::table('permisos')->where('id',$pid)->delete();
        return redirect()->route('permissions.index');
    })->name('permissions.eliminar');
});
