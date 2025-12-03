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

        // Employee users for testing
        $demoEmployees = [
            ['name'=>'Empleado Demo','email'=>'empleado@example.com','numero'=>'EMP100'],
            ['name'=>'Usuario Prueba 1','email'=>'usuario1@example.com','numero'=>'EMP101'],
            ['name'=>'Usuario Prueba 2','email'=>'usuario2@example.com','numero'=>'EMP102'],
        ];
        foreach ($demoEmployees as $emp) {
            $userId = DB::table('users')->where('email',$emp['email'])->value('id');
            if (!$userId) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $emp['name'],
                    'email' => $emp['email'],
                    'password' => Hash::make('password'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            DB::table('rol_usuario')->updateOrInsert(['user_id'=>$userId,'rol_id'=>$rolEmpleadoId], []);

            // Link to empleados table (create if not exists)
            $exists = DB::table('empleados')->where('correo',$emp['email'])->exists();
            if (!$exists) {
                DB::table('empleados')->insert([
                    'numero_empleado' => $emp['numero'],
                    'nombre' => explode(' ', $emp['name'])[0],
                    'apellido' => explode(' ', $emp['name'])[1] ?? 'Demo',
                    'correo' => $emp['email'],
                    'fecha_ingreso' => $now->toDateString(),
                    'estado' => 'activo',
                    'user_id' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('empleados')->where('correo',$emp['email'])->update([
                    'user_id' => $userId,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
