<#
Diagnostico 403 para la app Laravel en IIS
Ejecutar como Administrador. Genera un log en C:\temp con la salida.
#>

if (-not ([bool]([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator))) {
    Write-Error "Ejecuta PowerShell como Administrador."
    exit 1
}

$siteName = 'Default Web Site'
$appAlias = 'app'    # Cambia a 'laravel' si necesitas diagnosticar esa ruta
$physicalPath = 'C:\inetpub\wwwroot\laravel\public'
$logDir = 'C:\temp'
if (-not (Test-Path $logDir)) { New-Item -Path $logDir -ItemType Directory | Out-Null }
$timestamp = (Get-Date).ToString('yyyyMMdd-HHmmss')
$outFile = Join-Path $logDir "diagnose-403-laravel-$timestamp.txt"

function Write-Log { param($s) Add-Content -Path $outFile -Value $s; Write-Output $s }

Write-Log "=== Diagnóstico 403 Laravel - $(Get-Date) ==="

Import-Module WebAdministration -ErrorAction Stop

# Website bindings
Write-Log "-- Bindings for site: $siteName --"
try {
    $bindings = Get-WebBinding -Name $siteName
    $bindings | Format-Table protocol, bindingInformation, hostHeader | Out-String | ForEach-Object { Write-Log $_ }
} catch { Write-Log "Error obteniendo bindings: $($_.Exception.Message)" }

# Website / App listing
Write-Log "-- WebApplications under site --"
try {
    Get-WebApplication -Site $siteName | Select-Object Path, ApplicationPool, PhysicalPath | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
} catch { Write-Log "Error listando aplicaciones: $($_.Exception.Message)" }

Write-Log "-- VirtualDirectories under site --"
try {
    Get-WebVirtualDirectory -Site $siteName | Select-Object Path, PhysicalPath | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
} catch { Write-Log "Error listando vdirs: $($_.Exception.Message)" }

# Authentication settings for the application path
$location = "$siteName/$appAlias"
Write-Log "-- Authentication settings for location: $location --"
foreach ($auth in @('anonymousAuthentication','windowsAuthentication','basicAuthentication','urlAuthorization')) {
    try {
        $prop = Get-WebConfigurationProperty -Filter "system.webServer/security/authentication/$auth" -Location $location -Name "*" -ErrorAction SilentlyContinue
        if ($prop) { Write-Log "$auth:`n$($prop | Format-List | Out-String)" } else { Write-Log "$auth: (no configurado a nivel de location)" }
    } catch { Write-Log "$auth: error -> $($_.Exception.Message)" }
}

# Default document
Write-Log "-- Default document list for location: $location --"
try {
    $dd = Get-WebConfigurationProperty -Filter "system.webServer/defaultDocument/files" -Location $location -Name '.'
    $dd | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
} catch { Write-Log "Error leyendo Default Document: $($_.Exception.Message)" }

# Handler mappings (look for php)
Write-Log "-- Handler mappings (looking for php) --"
try {
    $handlers = Get-WebConfiguration -Filter "system.webServer/handlers/add" -Location $location
    $handlers | Where-Object { $_.name -match 'php' -or $_.path -match '\.php' } | Select-Object name, path, verb, type | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
} catch { Write-Log "Error leyendo handlers: $($_.Exception.Message)" }

# Check physical path and index.php
Write-Log "-- Comprobando ruta física y existencia de index.php --"
Write-Log "PhysicalPath variable configured: $physicalPath"
if (Test-Path $physicalPath) {
    Write-Log "Physical path existe. Listing top files:"
    Get-ChildItem -Path $physicalPath -File | Select-Object Name, Length | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
    $indexExists = Test-Path (Join-Path $physicalPath 'index.php')
    Write-Log "index.php existe: $indexExists"
} else {
    Write-Log "Physical path NO existe: $physicalPath"
}

# ACLs: public, storage, bootstrap/cache
Write-Log "-- ACLs --"
foreach ($p in @($physicalPath, 'C:\inetpub\wwwroot\laravel\storage', 'C:\inetpub\wwwroot\laravel\bootstrap\cache')) {
    if (Test-Path $p) {
        Write-Log "ACL para: $p"
        try {
            $acl = Get-Acl -Path $p
            $acl.Access | Select-Object IdentityReference, FileSystemRights, AccessControlType, IsInherited | Format-Table | Out-String | ForEach-Object { Write-Log $_ }
        } catch { Write-Log "Error leyendo ACL para $p: $($_.Exception.Message)" }
    } else {
        Write-Log "Path no existe: $p"
    }
}

# Find IIS log folder for site
Write-Log "-- IIS logs (últimas líneas y entradas 403) --"
try {
    $siteObj = Get-Website -Name $siteName -ErrorAction Stop
    $siteId = $siteObj.id
    $w3Folder = Join-Path $env:SystemDrive 'inetpub\logs\LogFiles'
    $siteLogFolder = Join-Path $w3Folder ("W3SVC{0}" -f $siteId)
    if (Test-Path $siteLogFolder) {
        $latestLog = Get-ChildItem $siteLogFolder -File | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($latestLog) {
            Write-Log "Latest log: $($latestLog.FullName)"
            Write-Log "-- Últimas 200 líneas --"
            Get-Content $latestLog.FullName -Tail 200 | ForEach-Object { Write-Log $_ }
            Write-Log "-- Líneas que contienen ' 403 ' --"
            Get-Content $latestLog.FullName | Select-String ' 403 ' | ForEach-Object { Write-Log $_.ToString() }
        } else { Write-Log "No se encontraron archivos de log en $siteLogFolder" }
    } else { Write-Log "No existe carpeta de logs esperada: $siteLogFolder" }
} catch { Write-Log "Error buscando logs IIS: $($_.Exception.Message)" }

# HTTP test from server simulating Host header
Write-Log "-- Prueba HTTP local simulando Host header --"
try {
    $uri = 'http://localhost/app'
    Write-Log "Invocando: $uri (Host: www.daniel-virguez.com)"
    $resp = Invoke-WebRequest -Uri $uri -Headers @{ Host = 'www.daniel-virguez.com' } -UseBasicParsing -TimeoutSec 15 -ErrorAction Stop
    Write-Log "Respuesta local: $($resp.StatusCode) $($resp.StatusDescription)"
} catch {
    Write-Log "Invoke-WebRequest falló: $($_.Exception.Message)"
}

Write-Log "=== Fin diagnóstico ==="

Write-Output "Diagnóstico completado. Salida guardada en: $outFile"
