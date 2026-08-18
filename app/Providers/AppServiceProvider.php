<?php

namespace App\Providers;

use App\Enums\RoleName;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole(RoleName::SuperAdmin->value) ? true : null;
        });

        // Genera URLs https:// aunque la request llegue por http (típico
        // detrás de un reverse proxy que termina TLS) — ver TRUSTED_PROXIES
        // en bootstrap/app.php para que Laravel confíe en ese proxy.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
