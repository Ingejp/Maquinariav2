<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Spatie no las auto-registra en el bootstrap/app.php de Laravel 11+.
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // También registra el log de 403 (ver hallazgo A09 OWASP) — basado en
        // status code de respuesta, no en report() de la excepción, porque
        // UnauthorizedException extiende Symfony\HttpException y Laravel
        // excluye esa familia del reporte por defecto (la trata como "esperada").
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Vacío por defecto (no confía en ningún proxy). Si en producción hay
        // un reverse proxy / load balancer terminando TLS, definir
        // TRUSTED_PROXIES en .env (IPs separadas por coma, o "*").
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES') === '*'
                ? '*'
                : array_filter(explode(',', (string) env('TRUSTED_PROXIES', ''))),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
