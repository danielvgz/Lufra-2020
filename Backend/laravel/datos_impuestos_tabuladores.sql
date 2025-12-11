-- Datos de ejemplo para Impuestos
INSERT INTO impuestos (nombre, codigo, porcentaje, descripcion, activo, por_defecto, created_at, updated_at) VALUES
('IVA', 'IVA', 16.00, 'Impuesto al Valor Agregado', 1, 1, NOW(), NOW()),
('ISLR', 'ISLR', 3.00, 'Impuesto Sobre la Renta', 1, 0, NOW(), NOW()),
('SSO', 'SSO', 4.00, 'Seguro Social Obligatorio', 1, 0, NOW(), NOW());

-- Datos de ejemplo para Tabuladores Salariales
INSERT INTO tabuladores_salariales (nombre, cargo, frecuencia, sueldo_base, moneda, descripcion, activo, created_at, updated_at) VALUES
('Tabulador Gerencial Mensual', 'Gerente', 'mensual', 5000.00, 'USD', 'Sueldo base para cargos gerenciales con pago mensual', 1, NOW(), NOW()),
('Tabulador Gerencial Quincenal', 'Gerente', 'quincenal', 2500.00, 'USD', 'Sueldo base para cargos gerenciales con pago quincenal', 1, NOW(), NOW()),
('Tabulador Administrativo Mensual', 'Asistente Administrativo', 'mensual', 1500.00, 'USD', 'Sueldo base para personal administrativo mensual', 1, NOW(), NOW()),
('Tabulador Administrativo Quincenal', 'Asistente Administrativo', 'quincenal', 750.00, 'USD', 'Sueldo base para personal administrativo quincenal', 1, NOW(), NOW()),
('Tabulador Operativo Semanal', 'Operador', 'semanal', 200.00, 'USD', 'Sueldo base para personal operativo con pago semanal', 1, NOW(), NOW()),
('Tabulador Operativo Quincenal', 'Operador', 'quincenal', 400.00, 'USD', 'Sueldo base para personal operativo quincenal', 1, NOW(), NOW()),
('Tabulador Operativo Mensual', 'Operador', 'mensual', 800.00, 'USD', 'Sueldo base para personal operativo mensual', 1, NOW(), NOW()),
('Salario Mínimo Mensual VES', 'General', 'mensual', 130.00, 'VES', 'Salario mínimo mensual en bolívares', 1, NOW(), NOW()),
('Salario Mínimo Quincenal VES', 'General', 'quincenal', 65.00, 'VES', 'Salario mínimo quincenal en bolívares', 1, NOW(), NOW());
