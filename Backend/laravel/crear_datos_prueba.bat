@echo off
echo ========================================
echo   CREANDO DATOS DE PRUEBA
echo ========================================
echo.

echo Ejecutando seeder...
php artisan db:seed --class=DatosPruebaSeeder

echo.
echo ========================================
echo   PROCESO COMPLETADO
echo ========================================
echo.
echo Datos creados:
echo   - 6 periodos de nomina (ultimos 6 meses)
echo   - Recibos para todos los empleados
echo   - Multiples pagos por recibo
echo   - Notificaciones de prueba
echo   - Departamentos adicionales
echo.
pause
