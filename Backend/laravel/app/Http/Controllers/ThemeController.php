<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ThemeController extends Controller
{
    public function index()
    {
        // Obtener themes desde la DB
        $dbThemes = Theme::all()->keyBy('slug');

        // Escanear directorio public/themes para detectar themes presentes en FS
        $fsPath = public_path('themes');
        $fsThemes = [];
        if (is_dir($fsPath)) {
            $dirs = array_filter(scandir($fsPath), function ($d) use ($fsPath) {
                return $d !== '.' && $d !== '..' && is_dir($fsPath . DIRECTORY_SEPARATOR . $d);
            });
            foreach ($dirs as $slug) {
                $name = $slug;
                // si existe un package.json o theme.json dentro podemos leer metadata (opcional)
                $metaPath = $fsPath . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'theme.json';
                $meta = null;
                if (file_exists($metaPath)) {
                    $content = file_get_contents($metaPath);
                    $meta = json_decode($content, true);
                    if (isset($meta['name'])) {
                        $name = $meta['name'];
                    }
                }
                $fsThemes[$slug] = (object)[
                    'name' => $name,
                    'slug' => $slug,
                    'installed_at' => null,
                    'active' => false,
                    'meta' => $meta,
                ];
            }
        }

        // Combinar: priorizar registros DB, pero incluir carpetas FS no registradas
        $combined = [];
        // Start with filesystem entries
        foreach ($fsThemes as $slug => $obj) {
            if ($dbThemes->has($slug)) {
                $combined[$slug] = $dbThemes->get($slug);
            } else {
                $combined[$slug] = $obj;
            }
        }

        // Include DB-only entries (possible if deleted from FS)
        foreach ($dbThemes as $slug => $theme) {
            if (!isset($combined[$slug])) {
                $combined[$slug] = $theme;
            }
        }

        // Convert to indexed array and sort by installed_at desc (nulls last)
        $themes = array_values($combined);
        usort($themes, function ($a, $b) {
            $ta = $a->installed_at ? strtotime($a->installed_at) : 0;
            $tb = $b->installed_at ? strtotime($b->installed_at) : 0;
            return $tb <=> $ta;
        });

        return view('themes.index', compact('themes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'zip' => 'required|file|mimes:zip|max:51200' // max 50MB
        ]);

        $file = $request->file('zip');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($originalName);

        // Ensure unique slug by appending suffix if exists
        $base = $slug;
        $i = 1;
        while (Theme::where('slug', $slug)->exists() || File::exists(public_path('themes/' . $slug))) {
            $slug = $base . '-' . $i++;
        }

        $targetDir = public_path('themes/' . $slug);
        File::ensureDirectoryExists($targetDir, 0755, true);

        // Prefer ZipArchive when available (safer). If not available, attempt system extraction as fallback.
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                // Extract safely: iterate entries
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    // Reject entries with ../ or absolute paths
                    if (Str::contains($entry, ['..', '\\'])) {
                        $zip->close();
                        return redirect()->back()->withErrors(['zip' => 'El archivo ZIP contiene rutas no permitidas.']);
                    }
                    $entryPath = $targetDir . DIRECTORY_SEPARATOR . $entry;
                    $dir = dirname($entryPath);
                    if (!File::exists($dir)) {
                        File::ensureDirectoryExists($dir, 0755, true);
                    }
                    $stream = $zip->getStream($entry);
                    if (!$stream) { continue; }
                    $outFile = fopen($entryPath, 'w');
                    while (!feof($stream)) {
                        fwrite($outFile, fread($stream, 1024));
                    }
                    fclose($outFile);
                    fclose($stream);
                }
                $zip->close();
            } else {
                return redirect()->back()->withErrors(['zip' => 'No se pudo abrir el archivo ZIP.']);
            }
        } else {
            // Fallback: try system 'unzip' or PowerShell Expand-Archive on Windows
            $tmpPath = $file->getRealPath();
            $cmd = null;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Use PowerShell Expand-Archive
                $cmd = 'powershell -NoProfile -Command "Expand-Archive -LiteralPath ' . escapeshellarg($tmpPath) . ' -DestinationPath ' . escapeshellarg($targetDir) . ' -Force"';
            } else {
                // Use unzip if available
                $cmd = 'unzip -o ' . escapeshellarg($tmpPath) . ' -d ' . escapeshellarg($targetDir);
            }

            // Execute command
            exec($cmd . ' 2>&1', $output, $rc);
            if ($rc !== 0) {
                // Cleanup partial extraction
                if (File::exists($targetDir)) {
                    File::deleteDirectory($targetDir);
                }
                return redirect()->back()->withErrors(['zip' => 'No se pudo extraer el ZIP en el servidor. Habilite la extensión Zip de PHP o instale unzip/PowerShell.']);
            }

            // Validate that extracted files are inside target dir (no traversal)
            $realTarget = realpath($targetDir);
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($it as $f) {
                $real = realpath($f->getPathname());
                if ($real === false || strpos($real, $realTarget) !== 0) {
                    // unsafe extraction detected, remove and abort
                    File::deleteDirectory($targetDir);
                    return redirect()->back()->withErrors(['zip' => 'El archivo ZIP contiene entradas no permitidas.']);
                }
            }
        }

        $theme = Theme::create([
            'name' => $originalName,
            'slug' => $slug,
            'installed_at' => now(),
            'active' => false,
            'meta' => null
        ]);

        return redirect()->back()->with('status', "Tema '{$theme->name}' instalado.");
    }

    public function activate(Theme $theme)
    {
        // deactivate others
        Theme::query()->update(['active' => false]);
        $theme->active = true;
        $theme->save();

        // persist in settings (web_template)
        $s = Settings::firstOrCreate(['key' => 'web_template']);
        $s->value = $theme->slug;
        $s->save();

        return redirect()->back()->with('status', "Tema '{$theme->name}' activado.");
    }

    public function destroy(Theme $theme)
    {
        // Only allow delete if not active
        if ($theme->active) {
            return redirect()->back()->withErrors(['theme' => 'No se puede eliminar un tema activo. Desactívelo primero.']);
        }

        $path = public_path('themes/' . $theme->slug);
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
        $theme->delete();
        return redirect()->back()->with('status', 'Tema eliminado.');
    }

    /**
     * Sync filesystem themes (public/themes) into the DB by creating missing records.
     * This is a convenience endpoint for local testing.
     */
    public function sync()
    {
        $fsPath = public_path('themes');
        if (!is_dir($fsPath)) {
            return redirect()->back()->withErrors(['themes' => 'No existe el directorio public/themes']);
        }

        $dirs = array_filter(scandir($fsPath), function ($d) use ($fsPath) {
            return $d !== '.' && $d !== '..' && is_dir($fsPath . DIRECTORY_SEPARATOR . $d);
        });

        $created = 0;
        foreach ($dirs as $slug) {
            if (!Theme::where('slug', $slug)->exists()) {
                Theme::create([
                    'name' => $slug,
                    'slug' => $slug,
                    'installed_at' => now(),
                    'active' => false,
                    'meta' => null,
                ]);
                $created++;
            }
        }

        return redirect()->back()->with('status', "Sincronización completada. $created tema(s) registrados.");
    }

    /**
     * Register a single filesystem theme (by slug) into the DB.
     */
    public function register(string $slug)
    {
        $fsPath = public_path('themes/' . $slug);
        if (!is_dir($fsPath)) {
            return redirect()->back()->withErrors(['theme' => 'No se encontró la carpeta del tema: ' . $slug]);
        }

        if (Theme::where('slug', $slug)->exists()) {
            return redirect()->back()->with('status', 'El tema ya está registrado.');
        }

        // attempt to read theme.json for nicer name
        $name = $slug;
        $metaPath = $fsPath . DIRECTORY_SEPARATOR . 'theme.json';
        $meta = null;
        if (file_exists($metaPath)) {
            $content = file_get_contents($metaPath);
            $meta = json_decode($content, true);
            if (!empty($meta['name'])) {
                $name = $meta['name'];
            }
        }

        $theme = Theme::create([
            'name' => $name,
            'slug' => $slug,
            'installed_at' => now(),
            'active' => false,
            'meta' => $meta,
        ]);

        return redirect()->back()->with('status', "Tema '{$theme->name}' registrado.");
    }

    /**
     * Deactivate a theme (set active=false) and clear web_template setting if it pointed to this theme.
     */
    public function deactivate(Theme $theme)
    {
        if (!$theme->active) {
            return redirect()->back()->with('status', 'El tema ya está desactivado.');
        }

        $theme->active = false;
        $theme->save();

        // If this was the web_template, clear it
        $s = Settings::where('key', 'web_template')->first();
        if ($s && $s->value === $theme->slug) {
            $s->value = null;
            $s->save();
        }

        return redirect()->back()->with('status', "Tema '{$theme->name}' desactivado.");
    }

    /**
     * Remove a filesystem theme folder by slug. Only allowed if theme is not active (if registered).
     */
    public function removeFolder(string $slug)
    {
        $path = public_path('themes/' . $slug);
        if (!is_dir($path)) {
            return redirect()->back()->withErrors(['theme' => 'Carpeta del tema no encontrada.']);
        }

        // If there's a DB record and it's active, don't remove
        $theme = Theme::where('slug', $slug)->first();
        if ($theme && $theme->active) {
            return redirect()->back()->withErrors(['theme' => 'No se puede eliminar la carpeta de un tema activo. Desactívelo primero.']);
        }

        try {
            File::deleteDirectory($path);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['theme' => 'Error al eliminar la carpeta: ' . $e->getMessage()]);
        }

        // If DB record exists and not active, delete record as well
        if ($theme && !$theme->active) {
            $theme->delete();
        }

        return redirect()->back()->with('status', "Carpeta del tema '{$slug}' eliminada.");
    }
}
