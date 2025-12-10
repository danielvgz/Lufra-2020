<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationHelper
{
    /**
     * Notificar al empleado cuando se le crea un recibo de pago
     */
    public static function notifyReciboCreado($reciboId, $empleadoId)
    {
        $empleado = DB::table('empleados')->where('id', $empleadoId)->first();
        if (!$empleado || !$empleado->user_id) {
            return;
        }

        Notification::create([
            'user_id' => $empleado->user_id,
            'type' => 'recibo_creado',
            'title' => 'Nuevo Recibo de Pago',
            'message' => 'Se ha generado un nuevo recibo de pago para ti.',
            'data' => [
                'recibo_id' => $reciboId,
            ],
        ]);
    }

    /**
     * Notificar al administrador cuando un empleado acepta un recibo
     */
    public static function notifyReciboAceptado($pagoId, $empleadoId)
    {
        $empleado = DB::table('empleados')->where('id', $empleadoId)->first();
        if (!$empleado) {
            return;
        }

        $admins = self::getAdminUsers();
        $nombreEmpleado = trim(($empleado->nombre ?? '') . ' ' . ($empleado->apellido ?? ''));

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'recibo_aceptado',
                'title' => 'Pago Aceptado',
                'message' => "El empleado {$nombreEmpleado} ha aceptado su pago.",
                'data' => [
                    'pago_id' => $pagoId,
                    'empleado_id' => $empleadoId,
                ],
            ]);
        }
    }

    /**
     * Notificar al administrador cuando un empleado rechaza un recibo
     */
    public static function notifyReciboRechazado($pagoId, $empleadoId)
    {
        $empleado = DB::table('empleados')->where('id', $empleadoId)->first();
        if (!$empleado) {
            return;
        }

        $admins = self::getAdminUsers();
        $nombreEmpleado = trim(($empleado->nombre ?? '') . ' ' . ($empleado->apellido ?? ''));

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'recibo_rechazado',
                'title' => 'Pago Rechazado',
                'message' => "El empleado {$nombreEmpleado} ha rechazado su pago.",
                'data' => [
                    'pago_id' => $pagoId,
                    'empleado_id' => $empleadoId,
                ],
            ]);
        }
    }

    /**
     * Notificar cuando se crea un departamento
     */
    public static function notifyDepartamentoCreado($departamentoId, $nombreDepartamento, $creadorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_departamentos');
            $creador = DB::table('users')->where('id', $creadorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $creadorId) {
                    continue; // No notificar al creador
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'departamento_creado',
                    'title' => 'Departamento Creado',
                    'message' => "El administrador {$creador} ha creado el departamento: {$nombreDepartamento}.",
                    'data' => [
                        'departamento_id' => $departamentoId,
                        'accion' => 'creado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de departamento creado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar cuando se edita un departamento
     */
    public static function notifyDepartamentoEditado($departamentoId, $nombreDepartamento, $editorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_departamentos');
            $editor = DB::table('users')->where('id', $editorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $editorId) {
                    continue; // No notificar al editor
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'departamento_editado',
                    'title' => 'Departamento Editado',
                    'message' => "El administrador {$editor} ha editado el departamento: {$nombreDepartamento}.",
                    'data' => [
                        'departamento_id' => $departamentoId,
                        'accion' => 'editado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de departamento editado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar cuando se elimina un departamento
     */
    public static function notifyDepartamentoEliminado($nombreDepartamento, $eliminadorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_departamentos');
            $eliminador = DB::table('users')->where('id', $eliminadorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $eliminadorId) {
                    continue; // No notificar al eliminador
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'departamento_eliminado',
                    'title' => 'Departamento Eliminado',
                    'message' => "El administrador {$eliminador} ha eliminado el departamento: {$nombreDepartamento}.",
                    'data' => [
                        'accion' => 'eliminado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de departamento eliminado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar cuando se crea un contrato
     */
    public static function notifyContratoCreado($contratoId, $empleadoNombre, $creadorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_contratos');
            $creador = DB::table('users')->where('id', $creadorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $creadorId) {
                    continue; // No notificar al creador
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'contrato_creado',
                    'title' => 'Contrato Creado',
                    'message' => "El administrador {$creador} ha creado un contrato para el empleado: {$empleadoNombre}.",
                    'data' => [
                        'contrato_id' => $contratoId,
                        'accion' => 'creado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de contrato creado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar cuando se edita un contrato
     */
    public static function notifyContratoEditado($contratoId, $empleadoNombre, $editorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_contratos');
            $editor = DB::table('users')->where('id', $editorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $editorId) {
                    continue; // No notificar al editor
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'contrato_editado',
                    'title' => 'Contrato Editado',
                    'message' => "El administrador {$editor} ha editado el contrato del empleado: {$empleadoNombre}.",
                    'data' => [
                        'contrato_id' => $contratoId,
                        'accion' => 'editado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de contrato editado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar cuando se elimina un contrato
     */
    public static function notifyContratoEliminado($empleadoNombre, $eliminadorId)
    {
        try {
            $admins = self::getUsersWithPermission('gestionar_contratos');
            $eliminador = DB::table('users')->where('id', $eliminadorId)->value('name') ?? 'Administrador';

            foreach ($admins as $admin) {
                if ($admin->id == $eliminadorId) {
                    continue; // No notificar al eliminador
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'contrato_eliminado',
                    'title' => 'Contrato Eliminado',
                    'message' => "El administrador {$eliminador} ha eliminado el contrato del empleado: {$empleadoNombre}.",
                    'data' => [
                        'accion' => 'eliminado',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de contrato eliminado: ' . $e->getMessage());
        }
    }

    /**
     * Notificar a los administradores cuando se cierra un período con pagos pendientes
     */
    public static function notifyPeriodoCerradoConPagosPendientes($periodoId, $cantidadPendientes)
    {
        try {
            $periodo = DB::table('periodos_nomina')->where('id', $periodoId)->first();
            if (!$periodo) {
                return;
            }

            $admins = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'Administrador')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('users.*')
                ->get();

            foreach ($admins as $admin) {
                if (!$admin->id) {
                    continue;
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'periodo_cerrado_pagos_pendientes',
                    'title' => 'Período Cerrado con Pagos Pendientes',
                    'message' => "Se ha cerrado el período {$periodo->codigo} con {$cantidadPendientes} pago(s) pendiente(s) por asignar.",
                    'data' => [
                        'periodo_id' => $periodoId,
                        'cantidad_pendientes' => $cantidadPendientes,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear notificación de período cerrado con pagos pendientes: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los usuarios administradores
     */
    private static function getAdminUsers()
    {
        $rolAdminId = DB::table('roles')->where('nombre', 'administrador')->value('id');
        if (!$rolAdminId) {
            return collect();
        }

        return DB::table('users')
            ->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')
            ->where('rol_usuario.rol_id', $rolAdminId)
            ->select('users.id', 'users.name', 'users.email')
            ->get();
    }

    /**
     * Obtener usuarios con un permiso específico o rol administrador
     */
    private static function getUsersWithPermission($permisoNombre)
    {
        // Primero obtener el ID del permiso
        $permisoId = DB::table('permisos')->where('nombre', $permisoNombre)->value('id');
        
        // Si no existe el permiso, devolver solo administradores
        if (!$permisoId) {
            return self::getAdminUsers();
        }

        // Obtener usuarios que tienen el permiso (a través de sus roles)
        $usersWithPermission = DB::table('users')
            ->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')
            ->join('permiso_rol', 'rol_usuario.rol_id', '=', 'permiso_rol.rol_id')
            ->where('permiso_rol.permiso_id', $permisoId)
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        // Si no hay usuarios con ese permiso, devolver administradores
        if ($usersWithPermission->isEmpty()) {
            return self::getAdminUsers();
        }

        return $usersWithPermission;
    }
}
