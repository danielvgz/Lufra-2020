<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Models\Settings as SettingModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $activeTheme = SettingModel::where('key','web_template')->value('value');
            if ($activeTheme) {
                $themePath = public_path("themes/{$activeTheme}");
                if (is_dir($themePath)) {
                    View::addLocation($themePath);
                    // Allow referencing theme views via namespace 'active_theme::view'
                    View::addNamespace('active_theme', $themePath);
                }
            }
        } catch (\Throwable $e) {
            // don't break boot if settings table not available yet
        }
        Schema::defaultStringLength(191);
        
        // Configurar paginación con Bootstrap 4
        Paginator::useBootstrapFour();
        
        if (! $this->app->runningInConsole()) {
            // 'key' => 'value'
            $settings = Settings::all('key', 'value')
                ->keyBy('key')
                ->transform(function ($setting) {
                    return $setting->value;
                })
                ->toArray();
            config([
               'settings' => $settings
            ]);

            config(['app.name' => config('settings.app_name')]);
        }
        
        // Compartir variable $role con la vista layouts
        View::composer('layouts', function ($view) {
            $role = null;
            if (auth()->check()) {
                // Intentar obtener el rol del usuario autenticado
                $role = auth()->user()->role ?? null;
                // Si no existe, consultar desde la tabla rol_usuario
                if (!$role) {
                    $role = DB::table('rol_usuario')
                        ->join('roles','roles.id','=','rol_usuario.rol_id')
                        ->where('rol_usuario.user_id', auth()->id())
                        ->value('roles.nombre');
                }
            }
            $view->with('role', $role);
        });
    }
}
