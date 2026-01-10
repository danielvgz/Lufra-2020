# Lufra-2020 — Sistema de Nóminas (Laravel + Vue.js)



Descripción
------------
Lufra-2020 es un sistema de nóminas para gestionar pagos, deducciones y reportes de personal. Se desarrollará con Laravel (backend) y Vue.js (frontend). El sistema manejará monedas USD y Bolívares (Bs). El objetivo es cubrir las necesidades típicas de nómina: cálculo de sueldos, retenciones, generación de recibos, históricos y reportes fiscales/contables.

Características principales (ejemplo)
- Gestión de empleados y contratos.
- Cálculo automático de nómina (salarios, horas extras, deducciones).
- Manejo de múltiples monedas (USD y Bolívares) y registro de tipo de cambio.
- Generación de recibos y exportes (PDF, CSV).
- Roles y permisos: admin, RRHH, empleado.
- Historiales, reportes y auditoría.

Cronograma (propuesta)
----------------------
Ajusta según la disponibilidad del equipo y las fechas del curso.

- Fase 0 — Preparación (1 semana)
  - Definir alcance y funcionalidades mínimas (MVP).
  - Crear repositorio y plantillas (README, CONTRIBUTING, issues/tickets).
  - Configurar entorno local (Sail / Valet / Homestead) y dependencias iniciales.

- Fase 1 — Infraestructura y diseño (2 semanas)
  - Estructura del proyecto Laravel + Vue.js (API + SPA).
  - Diseño de base de datos: empleados, contratos, nóminas, movimientos, monedas, tipos de cambio.
  - Wireframes y diseño de UX para pantallas clave.
  - Entregable: documento de arquitectura y wireframes.

- Fase 2 — Backend (3 semanas)
  - Autenticación y autorización (roles).
  - Modelos y endpoints para empleados, contratos, nóminas, pagos y reportes.
  - Migraciones y seeders, manejo de moneda y tipo de cambio.
  - Pruebas unitarias básicas.
  - Entregable: API documentada (OpenAPI / Postman).

- Fase 3 — Frontend (3 semanas)
  - Configurar Vue.js (Router, Pinia/Vuex).
  - Implementación de vistas: panel RRHH, gestión empleados, creación/ver nóminas, historial de pagos.
  - Soporte para selección de moneda en vistas y conversión visual con tipo de cambio.
  - Entregable: cliente funcional conectado a la API.

- Fase 4 — Integración y pruebas (2 semanas)
  - Tests end-to-end (Cypress o similar).
  - Pruebas de cálculo de nóminas y validaciones contables.
  - Corrección de bugs y optimizaciones.
  - Entregable: demo funcional estable.

- Fase 5 — Despliegue y documentación (1 semana)
  - Preparar entorno de producción (hosting, SSL, variables de entorno).
  - CI/CD para pruebas y despliegue (GitHub Actions).
  - Documentación de usuario y manual técnico.

Tareas continuas
- Revisión de código y control de calidad.
- Gestión de issues y sprints (milestones).
- Reuniones de seguimiento (weekly stand-up).
- Monitoreo y ajustes del tipo de cambio si se aplica conversión automática.
