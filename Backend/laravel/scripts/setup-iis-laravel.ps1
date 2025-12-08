<#
  setup-iis-laravel.ps1
  Uso: Ejecutar como Administrador en el servidor IIS
  Esto crea AppPool, aplica la aplicación bajo Default Web Site apuntando a public,
  fija permisos NTFS en storage y bootstrap/cache, y abre puertos en Firewall.
#>

# --- Comprobar privilegios
if (-not ([bool]([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator))) {
    Write-Error "Este script debe ejecutarse como Administrador. Salir."
    exit 1
}

Import-Module WebAdministration -ErrorAction Stop

$siteName = 'Default Web Site'
$appAlias = 'laravel'
$physicalPath = 'C:\inetpub\wwwroot\laravel\public'
$appPool = 'LaravelAppPool'

# --- Crear o asegurar Application Pool
if (-not (Test-Path IIS:\AppPools\$appPool)) {
    Write-Output "Creando Application Pool: $appPool"
    New-WebAppPool -Name $appPool
} else {
    Write-Output "Application Pool ya existe: $appPool"
}

# Asegurar configuración del AppPool
Set-ItemProperty IIS:\AppPools\$appPool -Name managedRuntimeVersion -Value ''
Set-ItemProperty IIS:\AppPools\$appPool -Name processModel.identityType -Value 'ApplicationPoolIdentity'

# --- Añadir o actualizar la aplicación
# Comprobar si ya existe una Application o una Virtual Directory con el mismo alias
$existingApp = Get-WebApplication -Site $siteName | Where-Object { $_.Path -eq "/$appAlias" } -ErrorAction SilentlyContinue
$existingVDir = Get-WebVirtualDirectory -Site $siteName | Where-Object { $_.Path -eq "/$appAlias" } -ErrorAction SilentlyContinue

if ($null -ne $existingApp) {
    Write-Output "La aplicación /$appAlias ya existe. Actualizando ruta física y AppPool"
    $appPath = "IIS:\Sites\$siteName\$appAlias"
    Set-ItemProperty -Path $appPath -Name applicationPool -Value $appPool
    Set-ItemProperty -Path $appPath -Name physicalPath -Value $physicalPath
} else {
    if ($null -ne $existingVDir) {
        Write-Warning "Existe una Virtual Directory /$appAlias. La eliminaré para crear la Application correctamente."
        try {
            Remove-WebVirtualDirectory -Site $siteName -Name $appAlias -ErrorAction Stop
            Write-Output "Virtual Directory /$appAlias eliminada."
        } catch {
            Write-Error "No se pudo eliminar la Virtual Directory /${appAlias}: $($_.Exception.Message)"
            throw
        }
    }

    Write-Output "Creando aplicación /$appAlias en '$siteName' apuntando a $physicalPath"
    try {
        New-WebApplication -Site $siteName -Name $appAlias -PhysicalPath $physicalPath -ApplicationPool $appPool -ErrorAction Stop
        Write-Output "Aplicación /$appAlias creada correctamente."
    } catch {
        Write-Error "Error creando la aplicación /${appAlias}: $($_.Exception.Message)"
    }
}

# --- Permisos NTFS para storage y bootstrap/cache
$icaclsUser = "IIS AppPool\$appPool"
$folders = @("C:\inetpub\wwwroot\laravel\storage", "C:\inetpub\wwwroot\laravel\bootstrap\cache")
foreach ($f in $folders) {
    if (-not (Test-Path $f)) {
        Write-Warning "Carpeta no existe: $f - saltando"
        continue
    }
    Write-Output "Otorgando permisos (Modify) a $icaclsUser en $f"
    $grantString = "${icaclsUser}:(OI)(CI)M"
    & icacls $f /grant $grantString /T | Out-Null
}

# --- Asegurar web.config en public (solo aviso)
$webConfig = Join-Path $physicalPath 'web.config'
if (-not (Test-Path $webConfig)) {
    Write-Warning "No se encontró web.config en $physicalPath. Asegúrate de agregar las reglas de URL Rewrite."
} else {
    Write-Output "web.config encontrado en $physicalPath"
}

# --- Reglas Firewall (80 y 443)
function Ensure-FWRule($name, $port) {
    $exists = Get-NetFirewallRule -DisplayName $name -ErrorAction SilentlyContinue
    if ($null -eq $exists) {
        New-NetFirewallRule -DisplayName $name -Direction Inbound -Protocol TCP -LocalPort $port -Action Allow -Profile Any
        Write-Output "Creada regla FW: $name (TCP $port)"
    } else {
        Write-Output "Regla FW ya existe: $name"
    }
}

Ensure-FWRule -name "IIS HTTP (80)" -port 80
Ensure-FWRule -name "IIS HTTPS (443)" -port 443

# --- Reiniciar IIS (opcional)
Write-Output "Se recomienda reiniciar IIS. Ejecutar 'iisreset' si es apropiado."

Write-Output "Hecho. Verifica:"
Write-Output " - En navegador: https://www.daniel-virguez.com/laravel"
Write-Output " - Que el binding HTTPS en IIS esté creado y tenga certificado para www.daniel-virguez.com"
Write-Output " - Que DNS apunte al servidor o editar hosts para pruebas locales"

exit 0
