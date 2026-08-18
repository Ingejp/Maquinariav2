# Maquinaria

Sistema de control de maquinaria agrícola/industrial por yardas (campos): catálogos de maquinaria, bitácora de reporte de estado operativo, y dashboard de reportes.

Reconstrucción completa (no migración incremental) del sistema `Ingejp/TFPB-Maquinaria` original (Laravel 8 / PHP 7.3 / Vue 2), sobre un stack actualizado. La base de datos productiva es la instancia existente en AWS RDS; este proyecto se conecta directamente a ella.

## Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Vue 3 + Vite, patrón Blade + islas de Vue (sin SPA completa, sin Inertia)
- **UI:** Tailwind CSS 4
- **RBAC:** `spatie/laravel-permission`
- **Base de datos:** MySQL (AWS RDS existente en producción; SQLite en desarrollo local sin acceso a RDS)
- **Gráficas:** Chart.js

## Requisitos

- PHP 8.3+
- Composer 2
- Node 20+ y npm
- Acceso a la instancia RDS (para desarrollo/producción con datos reales) o SQLite (para desarrollo local aislado)

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Edita `.env` con las credenciales reales de conexión (ver `.env.example` para las variables requeridas, incluyendo `DB_*` y `MYSQL_ATTR_SSL_CA` para la conexión TLS a RDS). Sin esas credenciales, deja `DB_CONNECTION=sqlite` para desarrollo local aislado.

```bash
php artisan migrate
npm run build   # o: npm run dev
php artisan serve
```

## Checklist de despliegue a pre-producción/producción

Esta va a ser la **primera vez** que la app corre contra la RDS real — `php artisan migrate` va a ejecutar *todas* las migraciones del proyecto de una sola vez (crea `sessions`/`cache`/`jobs`/tablas de Spatie/soft-deletes+índices en `report_status`; `users`/`failed_jobs` se saltan solos porque ya existen, ver `docs/decisiones-proyecto.md`).

1. **`.env` de producción** (no reusar el de local):
   - `APP_ENV=production`, `APP_DEBUG=false` (con `debug=true` en prod se filtran stack traces al usuario).
   - `APP_URL=https://tu-dominio-real`.
   - `DB_*` apuntando a la RDS real + `MYSQL_ATTR_SSL_CA` con el certificado (conexión TLS).
   - `SESSION_SECURE_COOKIE=true` (descomentar — con HTTPS real ya no rompe nada, y sin esto la sesión viaja insegura).
   - `TRUSTED_PROXIES`: si hay un reverse proxy/load balancer terminando el SSL del dominio delante de la app, poner sus IPs (o `*` si es la única forma de llegar a la app) — si no, Laravel puede no detectar que la conexión real es HTTPS.
   - `ADMIN_USERNAME`/`ADMIN_PASSWORD`: no hace falta, el usuario `admin` ya existe en la RDS real.
2. **Migrar:** `php artisan migrate` — revisar el output, debería mostrar las migraciones nuevas corriendo limpio (sin colisión con el esquema legacy).
3. **Seed de roles y permisos:** `php artisan db:seed --class=RoleSeeder` y `php artisan db:seed --class=PermissionSeeder`.
4. **Crítico — sincronizar roles legacy:** `php artisan legacy:sync-roles`. Sin este paso, los usuarios reales (`admin`, `JP`, `jose1`, etc.) no tienen ningún rol de Spatie asignado y quedan bloqueados de todo el sistema excepto lo que Super Admin haga por bypass. Verificar después con Tinker que cada usuario real tiene el rol esperado (ver `docs/decisiones-proyecto.md` para la asignación por defecto de permisos por rol).
5. **Build de assets:** `npm run build`.
6. **`TestUserSeeder` no se ejecuta** en producción aunque corras `db:seed` completo (guardado a `local`/`testing` — ver `docs/decisiones-proyecto.md`), así que no hace falta excluirlo a mano.
7. **QA manual con un usuario real de cada rol** (no solo Super Admin, que hace bypass de todos los permisos): confirmar que un `SUPERVISOR` puede reportar/ver dashboard pero no gestionar catálogos ni seguridad, y que un `ADMINISTRADOR` sí puede.
8. El certificado SSL del dominio en sí (Let's Encrypt, etc.) se gestiona a nivel de servidor web/proxy — fuera del alcance de la app.

## Estructura del proyecto

```
app/
  Http/Controllers/
    Auth/            (autenticación — sin registro público, sin recuperación de contraseña)
    Catalogs/         (Field, Status, MachineryType, Machinery)
    Security/          (Role, Permission, asignación de rol a usuario)
  Http/Requests/Catalogs/
  Http/Resources/Catalogs/
  Models/
    Catalogs/

resources/
  js/app/
    components/         (common, configuration, dashboard, report, security)
    pages/                (un componente raíz Vue por vista/isla)
    router/                (Vue Router por isla, no navegación global SPA)
    services/               (wrappers de Axios por recurso)
    utils/
  views/
    components/layouts/    (layout Blade base — <x-layouts.app>)
```

Patrón de integración: cada vista Blade "cascarón" declara `<div id="app" data-page="<carpeta>">`, y `resources/js/app.js` monta dinámicamente el componente en `resources/js/app/pages/<carpeta>/index.vue`.

## Decisiones de producto (cerradas, no reevaluar)

- No hay registro público de usuarios (`/register`) — el alta la hace el administrador.
- No hay ninguna funcionalidad de recuperación de contraseña dentro del sistema (ni autoservicio ni administrativa). El usuario contacta al administrador fuera de la aplicación.
- Sin CI/CD, sin Docker/IaC — despliegue manual tradicional.
- Sin migración de datos: la app apunta directamente a la RDS existente.

Ver el análisis técnico completo del sistema original y el plan de reconstrucción por fases en [`docs/analisis-tecnico-TFPB-Maquinaria.md`](docs/analisis-tecnico-TFPB-Maquinaria.md), y las decisiones tomadas durante la reconstrucción (que amplían o cierran puntos abiertos de ese análisis) en [`docs/decisiones-proyecto.md`](docs/decisiones-proyecto.md).

## Estrategia responsive

No es uniforme entre módulos — depende de cómo se usa cada uno en campo:

- **Reportar** y el **resumen del Dashboard** (KPIs básicos): mobile-first — los operarios reportan desde el celular en la yarda.
- **Gráficas y reportes detallados del Dashboard**: desktop-only — no se comprimen a mobile, se muestra un aviso de pantalla mínima requerida.
- **Configuración** y **Seguridad**: desktop-first, responsive (uso administrativo, no de campo).

Detalle completo en [`docs/decisiones-proyecto.md`](docs/decisiones-proyecto.md).
