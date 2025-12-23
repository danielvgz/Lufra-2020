<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
//use App\Models\Groups;
//use App\Models\Grouped;
//use App\Models\Positions;
//use App\Models\Currencies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Settings as SettingModel;

class SettingController extends Controller
{
    public function index()
    {
        // Listar plantillas disponibles en resources/views/templates
        $templatesPath = resource_path('views/templates');
        $templates = [];
        if (File::exists($templatesPath)) {
            $files = File::files($templatesPath);
            foreach ($files as $f) {
                $name = $f->getFilename();
                if (str_ends_with($name, '.blade.php')) {
                    $templates[] = str_replace('.blade.php','', $name);
                }
            }
        }

        // Valor actualmente seleccionado (preferir tabla settings)
        $current = SettingModel::where('key','web_template')->value('value') ?? config('settings.web_template');
        $currentUseHome = SettingModel::where('key','use_home_view')->value('value') ?? config('settings.use_home_view');
        // also provide the global show_notifications value so admins can toggle it
        $currentShowNotifications = SettingModel::where('key','show_notifications')->value('value') ?? config('settings.show_notifications');

        return view('settings', compact('templates','current','currentUseHome','currentShowNotifications'));
    }

    /**
     * Preview a template stored in resources/views/templates
     */
    public function previewTemplate(string $name)
    {
        $viewName = 'templates.' . $name;
        if (view()->exists($viewName)) {
            return view($viewName);
        }
        abort(404);
    }

    /**
     * Delete a template blade file from resources/views/templates and its public assets.
     */
    public function deleteTemplate(string $name)
    {
        // Authorization: only admins may delete templates
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $role = $user->role ?? null;
        if (!$role) {
            try {
                $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
                    ->join('roles','roles.id','=','rol_usuario.rol_id')
                    ->where('rol_usuario.user_id', $user->id)
                    ->value('roles.nombre');
            } catch (\Throwable $e) {
                $role = null;
            }
        }

        if (!($role && strtolower($role) === 'administrador')) {
            abort(403, 'Acceso denegado. Se requieren privilegios de administrador.');
        }

        // sanitize name: only allow simple filenames (no slashes)
        $clean = basename($name);
        if ($clean !== $name) {
            return redirect()->back()->withErrors(['template' => 'Nombre de plantilla inválido.']);
        }

        $bladePath = resource_path('views/templates/' . $clean . '.blade.php');
        if (!file_exists($bladePath)) {
            return redirect()->back()->withErrors(['template' => 'La plantilla no existe.']);
        }

        try {
            // delete blade file
            unlink($bladePath);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['template' => 'Error al eliminar la plantilla: ' . $e->getMessage()]);
        }

        // remove public assets folder if exists
        $publicPath = public_path('templates/' . $clean);
        if (is_dir($publicPath)) {
            // recursively delete
            try {
                \Illuminate\Support\Facades\File::deleteDirectory($publicPath);
            } catch (\Throwable $e) {
                // ignore error but report
                return redirect()->back()->with('status', 'Plantilla eliminada del resources pero fallo al borrar assets públicos: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('status', "Plantilla '{$clean}' eliminada correctamente.");
    }

    public function store(Request $request, Settings $settings )
    {
       
       if ($image = $request->file('image')) {
            $destinationPath = 'storage/settings/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
        }else {
             $profileImage = config('settings.image');
        }
        
        $data = $request->except('_token');
        $data['image'] = $profileImage;

        // Manejar checkbox use_home_view (usar vista `home` cuando esté marcado)
        $data['use_home_view'] = $request->has('use_home_view') ? '1' : '0';

        // Manejar checkbox show_notifications (global): solo permitir que administradores lo modifiquen
        try {
            $isAdmin = auth()->check() && (auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('administrador'));
        } catch (\Throwable $e) {
            $isAdmin = false;
        }
        if ($isAdmin) {
            $data['show_notifications'] = $request->has('show_notifications') ? '1' : '0';
        } else {
            // remover si viene en el request para evitar cambios por usuarios no autorizados
            if (array_key_exists('show_notifications', $data)) {
                unset($data['show_notifications']);
            }
        }
        
        foreach ($data as $key => $value) {
            $setting = Settings::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }
        return redirect()->route('settings.index')->with('status', 'Configuración actualizada correctamente.');
    }
}
