@echo off
echo Limpiando caches de Laravel...
echo.

php artisan cache:clear
echo Cache de aplicacion limpiado

php artisan route:clear
echo Cache de rutas limpiado

php artisan view:clear
echo Cache de vistas limpiado

php artisan config:clear
echo Cache de configuracion limpiado

echo.
echo ================================
echo Caches limpiados exitosamente!
echo ================================
echo.
echo Ahora recarga la pagina con Ctrl+F5
echo.
pause
