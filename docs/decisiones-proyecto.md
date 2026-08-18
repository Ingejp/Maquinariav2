# Decisiones del proyecto (post-análisis)

Bitácora de decisiones tomadas durante la reconstrucción que no estaban cerradas en `docs/analisis-tecnico-TFPB-Maquinaria.md`, o que lo amplían. Se agrega una entrada por decisión, en orden cronológico.

---

## 2026-08-17 — Estrategia responsive por módulo (mobile-first vs desktop)

**Decisión:** el diseño no es uniforme entre módulos — se define por cómo se usa cada uno en la operación real.

| Módulo | Estrategia | Motivo |
|---|---|---|
| **Reportar** (Yarda → Tipo → Máquina → Registrar) | **Mobile-first** | Los operarios reportan el estado de la maquinaria desde el celular, parados en el campo/yarda. Es el flujo que más se usa fuera de un escritorio. |
| **Dashboard — resumen/KPIs básicos** | **Mobile-first** | Un supervisor debe poder revisar el estado general (resumen por tipo de maquinaria, alertas) desde el celular sin depender de una computadora. |
| **Dashboard — gráficas y reportes detallados** | **Desktop-only** | Las vistas de Chart.js con filtros de rango de fechas/campo y tablas detalladas no se adaptan a mobile; se diseñan para pantalla grande. En viewport mobile se debe mostrar un aviso ("esta vista requiere una pantalla más grande") en vez de forzar un layout roto. |
| **Configuración (catálogos: Field, Status, MachineryType, Machinery)** | Desktop-first, responsive | Uso administrativo, pero sin restricción dura — debe verse usable en tablet/mobile si hace falta, sin ser el caso de uso principal. |
| **Seguridad (roles, permisos, asignación)** | Desktop-first, responsive | Mismo criterio que Configuración — administración, no operación de campo. |

**Cómo aplicarlo:** con Tailwind (mobile-first por diseño: clases base = mobile, prefijos `sm:`/`md:`/`lg:` = breakpoints mayores), esto se traduce en:
- Reportar y el resumen del Dashboard: se construyen primero para viewport mobile, y se amplían con breakpoints solo si aporta valor en pantallas grandes.
- Gráficas/reportes detallados del Dashboard: se construyen para desktop (`lg:`/`xl:` como base funcional) y explícitamente muestran un estado "no disponible en este tamaño de pantalla" por debajo de cierto breakpoint, en vez de intentar comprimir tablas/gráficas complejas a un viewport angosto.

**Afecta a:** Fase 2 (sistema de diseño, se define junto con la paleta/branding), Fase 3 (Reportar), Fase 4 (Dashboard).

---

## 2026-08-17 — Conexión a respaldo real de producción (MySQL local)

Se conectó la app a un respaldo restaurado en local (MySQL, `test_maquinaria`) de la base de datos de producción real, reemplazando el sqlite de desarrollo usado hasta ahora. Esto confirmó en la práctica varios supuestos de la Fase 1 y obligó dos ajustes:

**Hallazgos al conectar:**
- El respaldo confirma el esquema documentado en la sección 4.2 del análisis: `users` ya existe con `id, username, password, role_id (NOT NULL), remember_token, timestamps` — sin `name` ni `email`, como se asumió.
- La tabla `role` tiene **4 filas, no 3**: además de `SUPER ADMIN` / `ADMINISTRADOR` / `SUPERVISOR`, existe `SUPERVISOR ESTICASA` (agregada 2024-09-17, probablemente para un cliente/subsidiaria específica). Se agregó como cuarto caso de `App\Enums\RoleName`.
- 7 usuarios reales, 159,113 filas en `report_status` — datos de producción a escala real, no un dataset de prueba.
- Los hashes de password son bcrypt (`$2a$12$...`), compatibles sin cambios con `Hash::check` de Laravel 13.
- La tabla `migrations` trae el historial de 12 migraciones del sistema Laravel 8 original (nombres de archivo distintos a los nuestros) — Laravel no las reconoce como "ya corridas" respecto a nuestros archivos nuevos, así que corre todos los nuestros como lote nuevo.

**Ajustes hechos:**
1. `database/migrations/0001_01_01_000000_create_users_table.php` y `..._create_jobs_table.php`: se agregó `Schema::hasTable(...)` antes de crear `users` y `failed_jobs`, que ya existen en el respaldo — evita colisión sin tocar su esquema real. `sessions`, `cache`, `jobs`, `job_batches` sí se crean (no existían). El `down()` de `users` deliberadamente **no** dropea la tabla — nunca debe poder borrar datos reales de un rollback.
2. Nuevo comando `php artisan legacy:sync-roles` (`app/Console/Commands/SyncLegacyRoles.php`): lee la tabla `role` y `users.role_id` legacy y asigna a cada usuario el rol de Spatie correspondiente (idempotente, se puede correr varias veces; no hace nada si no hay esquema legacy presente, por lo que es seguro dejarlo en el repo para cualquier entorno).

**Verificado:** migración limpia sin colisiones sobre el respaldo real; los 7 usuarios reales quedaron con su rol de Spatie correcto (`admin → SUPER ADMIN`, `JP → ADMINISTRADOR`, el resto → `SUPERVISOR`); login end-to-end probado en navegador contra la conexión MySQL real (con un usuario de prueba desechable, eliminado después — no se tocaron credenciales reales).

**Nota de seguridad:** se corrigió además un bug en `.env.example`: `SESSION_SECURE_COOKIE=true` estaba activo por defecto, lo cual rompe la sesión en desarrollo local por HTTP (`php artisan serve`). Se dejó comentado con instrucción de activarlo solo en producción bajo HTTPS.
