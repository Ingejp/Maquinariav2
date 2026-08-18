<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers básicos de seguridad (hallazgo A02 OWASP: security misconfiguration).
 * La CSP solo se aplica en producción para no interferir con el cliente de
 * Vite (HMR por WebSocket, módulos servidos desde otro origen) en desarrollo.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if (app()->environment('production')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
            ]));
        }

        // Hallazgo A09 OWASP: registrar 403 (permiso denegado). Basado en el
        // status code de la respuesta y no en report() de la excepción —
        // UnauthorizedException extiende Symfony\HttpException, que Laravel
        // excluye del reporte por defecto (se trata como "esperada").
        if ($response->getStatusCode() === 403 && auth()->check()) {
            Log::warning('Acceso denegado (403)', [
                'user_id' => auth()->id(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        return $response;
    }
}
