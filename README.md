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

Ver el análisis técnico completo del sistema original y el plan de reconstrucción por fases en [`docs/analisis-tecnico-TFPB-Maquinaria.md`](docs/analisis-tecnico-TFPB-Maquinaria.md).
