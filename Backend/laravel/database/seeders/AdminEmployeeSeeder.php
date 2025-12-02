<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Ensure roles exist
        $rolAdminId = DB::table('roles')->where('nombre','administrador')->value('id');
        if (!$rolAdminId) {
            $rolAdminId = DB::table('roles')->insertGetId([
                'nombre' => 'administrador',
                'descripcion' => 'Admin del sistema',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $rolEmpleadoId = DB::table('roles')->where('nombre','empleado')->value('id');
        if (!$rolEmpleadoId) {
            $rolEmpleadoId = DB::table('roles')->insertGetId([
                'nombre' => 'empleado',
                'descripcion' => 'Empleado',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Admin user
        $adminId = DB::table('users')->where('email','admin@example.com')->value('id');
        if (!$adminId) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        DB::table('rol_usuario')->updateOrInsert(['user_id'=>$adminId,'rol_id'=>$rolAdminId], []);

        // Employee user
        $empEmail = 'empleado@example.com';
        $empleadoUserId = DB::table('users')->where('email',$empEmail)->value('id');
        if (!$empleadoUserId) {
            $empleadoUserId = DB::table('users')->insertGetId([
                'name' => 'Empleado Demo',
                'email' => $empEmail,
                'password' => Hash::make('password'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        DB::table('rol_usuario')->updateOrInsert(['user_id'=>$empleadoUserId,'rol_id'=>$rolEmpleadoId], []);

        // Link to empleados table (create if not exists)
        $existeEmpleado = DB::table('empleados')->where('correo',$empEmail)->exists();
        if (!$existeEmpleado) {
            DB::table('empleados')->insert([
                'numero_empleado' => 'EMP100',
                'nombre' => 'Empleado',
                'apellido' => 'Demo',
                'correo' => $empEmail,
                'fecha_ingreso' => $now->toDateString(),
                'estado' => 'activo',
                'user_id' => $empleadoUserId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('empleados')->where('correo',$empEmail)->update([
                'user_id' => $empleadoUserId,
                'updated_at' => $now,
            ]);
        }
    }
}
