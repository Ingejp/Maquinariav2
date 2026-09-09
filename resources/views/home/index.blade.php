<x-layouts.app title="Inicio">
    <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6">

        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Bienvenido, {{ auth()->user()->username }}</p>
            <h1 class="font-display text-2xl font-semibold text-text">¿Qué vas a hacer hoy?</h1>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">

            @can('report.create')
            <a href="{{ route('report.index') }}" class="group flex flex-col items-center rounded-2xl border border-border bg-surface p-6 text-center transition hover:border-accent hover:shadow-sm">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-soft text-accent transition group-hover:bg-accent group-hover:text-white">
                    {{-- Portapapeles con lápiz --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <p class="font-display text-base font-semibold text-text">Reportar</p>
                <p class="mt-1 text-xs text-text-muted">Registrar el estado de la flota</p>
            </a>
            @endcan

            @can('dashboard.view')
            <a href="{{ route('dashboard.index') }}" class="group flex flex-col items-center rounded-2xl border border-border bg-surface p-6 text-center transition hover:border-accent hover:shadow-sm">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-soft text-accent transition group-hover:bg-accent group-hover:text-white">
                    {{-- Gráfica de barras --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <p class="font-display text-base font-semibold text-text">Dashboard</p>
                <p class="mt-1 text-xs text-text-muted">KPIs y detalle de reportes</p>
            </a>
            @endcan

            @can('catalogs.view')
            <a href="{{ route('catalogs.field.index') }}" class="group flex flex-col items-center rounded-2xl border border-border bg-surface p-6 text-center transition hover:border-accent hover:shadow-sm">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-soft text-accent transition group-hover:bg-accent group-hover:text-white">
                    {{-- Engranaje --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="font-display text-base font-semibold text-text">Configuración</p>
                <p class="mt-1 text-xs text-text-muted">Yardas, tipos, estados y máquinas</p>
            </a>
            @endcan

            @can('security.manage')
            <a href="{{ route('security.index') }}" class="group flex flex-col items-center rounded-2xl border border-border bg-surface p-6 text-center transition hover:border-accent hover:shadow-sm">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-accent-soft text-accent transition group-hover:bg-accent group-hover:text-white">
                    {{-- Escudo --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <p class="font-display text-base font-semibold text-text">Seguridad</p>
                <p class="mt-1 text-xs text-text-muted">Roles, permisos y usuarios</p>
            </a>
            @endcan

        </div>

    </main>
</x-layouts.app>
