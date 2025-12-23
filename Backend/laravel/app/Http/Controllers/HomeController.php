<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings as SettingModel;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Si está configurado para usar la vista 'home', mostrarla
        $useHome = SettingModel::where('key','use_home_view')->value('value') ?? config('settings.use_home_view');
        if ($useHome === '1' || $useHome === 1 || $useHome === true) {
            return view('home');
        }

        // Si no, intentar cargar la plantilla web seleccionada
        $selected = SettingModel::where('key','web_template')->value('value') ?? config('settings.web_template');
        if ($selected) {
            // Preferir plantillas en resources/views/templates
            if (view()->exists('templates.' . $selected)) {
                return view('templates.' . $selected, ['template' => $selected]);
            }

            // Si existe un namespace activo (extraído en public/themes/{slug}), probar archivos comunes
            // Priorizar: active_theme::home, active_theme::index, active_theme::{selected}, o vista directa {selected}
            if (view()->exists('active_theme::home')) {
                return view('active_theme::home', ['template' => $selected]);
            }
            if (view()->exists('active_theme::index')) {
                return view('active_theme::index', ['template' => $selected]);
            }
            if (view()->exists('active_theme::' . $selected)) {
                return view('active_theme::' . $selected, ['template' => $selected]);
            }

            // Finalmente tratar de renderizar por nombre simple (por si el tema incluye {slug}.blade.php en la carpeta añadida)
            if (view()->exists($selected)) {
                return view($selected, ['template' => $selected]);
            }
        }

        return view('home');
    }
}
