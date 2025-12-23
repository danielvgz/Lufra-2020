<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $userId = Auth::id();
            // Comprobar preferencia global y por usuario (el usuario puede sobreescribir)
            $global = Settings::where('key', 'show_notifications')->value('value');
            $pref = Settings::where('key', 'user_' . $userId . '_show_notifications')->value('value');

            // Lógica: if user explicit pref === '1' -> show; if pref === '0' -> hide; if pref is null -> follow global (show unless global === '0')
            $show = true;
            if (!is_null($pref)) {
                $show = ((string)$pref === '1');
            } else {
                $show = !(!is_null($global) && (string)$global === '0');
            }

            if (!$show) {
                return response()->json([
                    'notifications' => [],
                    'total' => 0,
                    'unread' => 0,
                ]);
            }
            \Log::info('Cargando notificaciones para usuario: ' . $userId);
            
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('read', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Notificaciones encontradas: ' . $notifications->count());

            return response()->json([
                'notifications' => $notifications,
                'total' => $notifications->count(),
                'unread' => $notifications->where('read', false)->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cargar notificaciones: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function unread()
    {
        $userId = Auth::id();
        // Comprobar preferencia global y por usuario para listado de no leidas
        $global = Settings::where('key', 'show_notifications')->value('value');
        $pref = Settings::where('key', 'user_' . $userId . '_show_notifications')->value('value');
        $show = true;
        if (!is_null($pref)) {
            $show = ((string)$pref === '1');
        } else {
            $show = !(!is_null($global) && (string)$global === '0');
        }
        if (!$show) {
            return response()->json([
                'notifications' => [],
                'count' => 0,
            ]);
        }
        $notifications = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function deleteRead()
    {
        $deleted = Notification::where('user_id', Auth::id())
            ->where('read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
