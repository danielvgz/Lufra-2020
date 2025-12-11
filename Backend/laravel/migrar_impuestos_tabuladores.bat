@echo off
echo Ejecutando migraciones para Impuestos y Tabuladores...
php artisan migrate --path=database/migrations/2025_12_11_000000_create_impuestos_table.php
php artisan migrate --path=database/migrations/2025_12_11_000001_create_tabuladores_salariales_table.php
php artisan migrate --path=database/migrations/2025_12_11_000002_add_impuesto_to_pagos.php
php artisan migrate --path=database/migrations/2025_12_11_000003_add_devengado_impuesto_to_recibos.php
echo.
echo Migraciones completadas!
echo.
echo Para cargar datos de ejemplo, ejecutar:
echo mysql -u root -p nombre_base_datos ^< datos_impuestos_tabuladores.sql
echo.
pause
