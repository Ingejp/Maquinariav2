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

---

## 2026-08-18 — Usuario fijo de QA (`TestUserSeeder`)

Hasta ahora, cada verificación manual en navegador creaba un usuario desechable por Tinker y lo borraba al terminar. Se reemplaza por un usuario fijo y reutilizable: `database/seeders/TestUserSeeder.php`, con rol `SUPER ADMIN` (bypass total vía `Gate::before`).

- Credenciales conocidas (no aleatorias, a diferencia de `AdminUserSeeder`): `TEST_USERNAME`/`TEST_PASSWORD` en `.env` (default `qa_test` / `QaTest#2026!` si no se definen).
- **Guard de entorno:** solo corre si `app()->environment(['local', 'testing'])` — nunca se crea esta cuenta si el entorno apunta a datos reales de producción, aunque el seeder esté en el repo.
- `updateOrCreate` en vez de "crear si no existe": correr el seeder de nuevo resetea la contraseña, útil si se necesita recuperar el acceso.
- Agregado a `DatabaseSeeder` (corre con `php artisan db:seed` normal, pero el guard de entorno lo hace inofensivo fuera de local).

**Verificado:** login en navegador con `qa_test` contra la BD MySQL real (restaurada en local) funciona correctamente.

---

## 2026-08-18 — RBAC real: permisos por módulo + bug crítico del middleware de Spatie

**Decisión de permisos:** en vez de replicar el patrón legacy de un permiso por combinación URL+verbo (la causa de hallazgo 5.5 — el seeder se olvidaba de generar `delete` y nadie podía borrar nada), se definieron 5 permisos por módulo en `PermissionSeeder`: `catalogs.view`, `catalogs.manage`, `report.create`, `dashboard.view`, `security.manage`. Mucha menos superficie para que un seeder futuro se equivoque.

Asignación por defecto (ajustable después desde la propia pantalla de Seguridad):
- **SUPER ADMIN**: los 5 (además del bypass de `Gate::before` — se le asignan explícitamente para que la UI no lo muestre como "sin permisos").
- **ADMINISTRADOR**: los 5.
- **SUPERVISOR** y **SUPERVISOR ESTICASA**: `catalogs.view`, `report.create`, `dashboard.view` (no `catalogs.manage` ni `security.manage`).

Antes de proteger ninguna ruta se verificó con Tinker que los 7 usuarios reales conservaban el acceso esperado según su rol — para no bloquear a nadie al desplegar esto.

**Bug crítico encontrado y corregido:** al aplicar `middleware('permission:...')` a las rutas, **todas** (incluidas las que ya funcionaban antes, como listar catálogos) empezaron a tirar 500 con `Target class [permission] does not exist`. Causa: en Laravel 11+/13 con `bootstrap/app.php` (sin `Kernel.php`), el alias de middleware `permission` de Spatie **no se auto-registra** — hay que declararlo a mano:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

Se detectó probando con un usuario `SUPERVISOR` real (rol, no Super Admin) vía `curl` con cookies de sesión reales antes de dar por cerrada la protección de rutas — si se hubiera desplegado sin esta prueba, **todos los usuarios reales habrían quedado bloqueados de todo el sistema**, no solo de lo que no debían ver.

**Verificado tras el fix:** con un usuario `SUPERVISOR` de prueba — `catalogs.view`/`dashboard.view`/`report.create` devuelven 200, un intento de `POST` a crear un campo (requiere `catalogs.manage`) devuelve 403 limpio. `qa_test` (SUPER ADMIN) conserva acceso total.

---

## 2026-08-18 — Fase 6 (Hardening) + Fase 7 (migraciones puntuales): checklist OWASP Top 10:2025

Repaso de la sección 11 del análisis técnico, con el estado real después de Fases 0-6. No se tocó la RDS real ni el dominio/SSL de producción — eso lo hace el usuario al desplegar a pre-producción.

| # | Categoría | Estado |
|---|---|---|
| A01 | Broken Access Control | **Resuelto.** RBAC con Spatie, 5 permisos por módulo, aplicados a todas las rutas de negocio, verificado con sesión real no-privilegiada (no solo Super Admin). Deniega por defecto: ruta nueva sin middleware `permission:` explícito queda abierta solo a `auth`, no a todo el mundo. |
| A02 | Security Misconfiguration | **Resuelto en código.** CORS deshabilitado (`paths => []`, no hay API pública). Headers de seguridad (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, CSP en producción) vía `SecurityHeaders` middleware. `.env.example` documenta `APP_DEBUG=false`/`APP_ENV=production` para el deploy real. **Pendiente del usuario:** que el `.env` de pre-producción realmente tenga esos valores — el código no puede forzarlo. |
| A03 | Software Supply Chain Failures | **Parcial.** Stack sin EOL (Laravel 13, Vue 3), `composer.lock`/`package-lock.json` comiteados. Sin CI/CD (decisión de alcance) → `composer audit`/`npm audit` quedan como paso manual antes de cada release, no automatizado. No se corrieron en esta sesión — recomendado antes del deploy. |
| A04 | Cryptographic Failures | **Resuelto.** `MYSQL_ATTR_SSL_CA` para TLS a RDS, `URL::forceScheme('https')` en producción, `SESSION_SECURE_COOKIE` documentado (comentado en local, activar en producción), passwords con cast `hashed` (bcrypt). El certificado SSL del dominio en sí lo gestiona el usuario en el servidor/proxy real. |
| A05 | Injection | **Resuelto.** Eloquent parametrizado en todo el proyecto; los `DB::raw`/`selectRaw` del Dashboard usan literales fijos (`DATE(...)`), nunca interpolan input de usuario. Vue 3 escapa por defecto, sin `v-html` en ningún componente. |
| A06 | Insecure Design | **Resuelto.** Decisiones de producto ya cerradas desde Fase 1 (sin registro público, sin recuperación de contraseña, alta solo por admin con password ≥10 caracteres); RBAC rediseñado con 5 permisos simples en vez del patrón legacy por-URL que generaba bugs. |
| A07 | Authentication Failures | **Resuelto.** Throttling en login (5 intentos/min por usuario+IP vía `LoginRequest`, más `throttle:10,1` a nivel de ruta), regeneración de sesión en login/logout, password mínima de 10 caracteres en altas nuevas. MFA no implementado — mejora opcional, fuera de alcance (así lo marcaba ya el análisis original). |
| A08 | Software or Data Integrity Failures | **Sin cambios de código — es una práctica operativa.** Despliegue manual (decisión de alcance, sin CI/CD); al desplegar, usar exactamente los mismos `composer.lock`/`package-lock.json` que se probaron, no regenerarlos en el servidor. |
| A09 | Security Logging and Alerting Failures | **Resuelto.** Se agregó logging explícito (no existía antes) de: intentos de login fallidos (`LoginRequest`), accesos denegados 403 (`SecurityHeaders` middleware — ver nota técnica abajo), y acciones administrativas sensibles (alta de usuario, cambio de rol, cambio de permisos de un rol). Verificado con `curl` que los 3 casos escriben en `storage/logs/laravel.log`. |
| A10 | Mishandling of Exceptional Conditions | **Resuelto.** `findOrFail`/route-model-binding consistente en todos los controladores desde que se construyeron (Fases 2-5, no fue necesario tocar nada nuevo). Excepciones JSON limpias sin stack trace cuando `APP_DEBUG=false` (comportamiento por defecto de Laravel, ya configurado desde Fase 0 vía `shouldRenderJsonWhen`). |

**Nota técnica sobre A09:** el primer intento de loguear los 403 fue con `$exceptions->report()` sobre `UnauthorizedException` en `bootstrap/app.php` — no funcionó. `UnauthorizedException` (Spatie) extiende `Symfony\HttpException`, y Laravel excluye esa familia del reporte por defecto (la trata como "esperada", no como error real). Se resolvió en cambio revisando el status code de la respuesta directamente dentro de `SecurityHeaders` middleware, que ya corre en todas las requests.

**Fase 7 — migraciones puntuales:** ver la entrada siguiente para el detalle de soft deletes + índices en `report_status`.
