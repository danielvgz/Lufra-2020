/*
  Versión adaptada para SQLite del esquema de nóminas.
  Cambios principales para compatibilidad SQLite:
   - `BIGINT PRIMARY KEY` -> `INTEGER PRIMARY KEY AUTOINCREMENT`
   - `VARCHAR(...)` -> `TEXT`
   - `DECIMAL(...)` -> `NUMERIC`
   - `BOOLEAN` -> `INTEGER` (0/1)
   - NOTA: Ajusta según necesites para otro motor (Postgres/MySQL).
*/

-- Tablas
CREATE TABLE departamentos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  codigo TEXT UNIQUE,
  nombre TEXT NOT NULL,
  descripcion TEXT,
  id_responsable INTEGER,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE puestos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  departamento_id INTEGER REFERENCES departamentos(id) ON DELETE SET NULL,
  titulo TEXT NOT NULL,
  descripcion TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE empleados (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  numero_empleado TEXT UNIQUE,
  nombre TEXT NOT NULL,
  apellido TEXT NOT NULL,
  correo TEXT UNIQUE,
  identificador_fiscal TEXT,
  fecha_nacimiento DATE,
  fecha_ingreso DATE,
  fecha_baja DATE,
  estado TEXT DEFAULT 'activo',
  telefono TEXT,
  direccion TEXT,
  banco TEXT,
  cuenta_bancaria TEXT,
  notas TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contratos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  empleado_id INTEGER NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
  puesto_id INTEGER REFERENCES puestos(id) ON DELETE SET NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE,
  tipo_contrato TEXT NOT NULL,
  frecuencia_pago TEXT NOT NULL,
  salario_base NUMERIC NOT NULL,
  moneda_pago TEXT DEFAULT 'EUR',
  horas_por_semana NUMERIC,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE periodos_nomina (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  codigo TEXT UNIQUE,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado TEXT DEFAULT 'abierto',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE componentes_salario (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  codigo TEXT UNIQUE,
  nombre TEXT NOT NULL,
  tipo TEXT NOT NULL,
  calculo TEXT DEFAULT 'fijo',
  valor NUMERIC DEFAULT 0.0,
  gravable INTEGER DEFAULT 1,
  visible_en_recibo INTEGER DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE recibos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  empleado_id INTEGER NOT NULL REFERENCES empleados(id) ON DELETE CASCADE,
  contrato_id INTEGER REFERENCES contratos(id) ON DELETE SET NULL,
  periodo_nomina_id INTEGER NOT NULL REFERENCES periodos_nomina(id) ON DELETE CASCADE,
  bruto NUMERIC DEFAULT 0.00,
  total_percepciones NUMERIC DEFAULT 0.00,
  total_deducciones NUMERIC DEFAULT 0.00,
  neto NUMERIC DEFAULT 0.00,
  estado TEXT DEFAULT 'borrador',
  emitido_en TIMESTAMP,
  pagado_en TIMESTAMP,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lineas_recibo (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  recibo_id INTEGER NOT NULL REFERENCES recibos(id) ON DELETE CASCADE,
  componente_id INTEGER REFERENCES componentes_salario(id) ON DELETE SET NULL,
  descripcion TEXT,
  importe NUMERIC NOT NULL,
  porcentaje NUMERIC,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasas_impuesto (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre TEXT NOT NULL,
  porcentaje NUMERIC NOT NULL,
  vigente_desde DATE,
  vigente_hasta DATE,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contribuciones (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre TEXT NOT NULL,
  tasa_empleado NUMERIC DEFAULT 0.0,
  tasa_empleador NUMERIC DEFAULT 0.0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pagos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  recibo_id INTEGER NOT NULL REFERENCES recibos(id) ON DELETE CASCADE,
  metodo TEXT NOT NULL,
  importe NUMERIC NOT NULL,
  pagado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  referencia TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registro_auditoria (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  usuario_identificador TEXT,
  accion TEXT NOT NULL,
  tabla TEXT,
  registro_id INTEGER,
  payload TEXT,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices recomendados
CREATE INDEX idx_empleados_numero ON empleados(numero_empleado);
CREATE INDEX idx_empleados_correo ON empleados(correo);
CREATE INDEX idx_recibos_empleado_periodo ON recibos(empleado_id, periodo_nomina_id);
CREATE INDEX idx_periodos_fechas ON periodos_nomina(fecha_inicio, fecha_fin);

-- Datos de ejemplo (opcionales)
-- INSERT INTO departamentos (codigo, nombre) VALUES ('RRHH', 'Recursos Humanos');
