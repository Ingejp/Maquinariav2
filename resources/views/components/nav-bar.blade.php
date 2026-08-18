@php
    $modules = [
        ['label' => 'Configuración', 'route' => 'catalogs.field.index', 'pattern' => 'catalogs.*'],
        ['label' => 'Reportar', 'route' => 'report.index', 'pattern' => 'report.*'],
        ['label' => 'Dashboard', 'route' => 'dashboard.index', 'pattern' => 'dashboard.*'],
        ['label' => 'Seguridad', 'route' => 'security.index', 'pattern' => 'security.*'],
    ];
@endphp

<nav class="flex flex-wrap items-center justify-between gap-3 bg-accent px-4 py-3 sm:px-6">
    <div class="flex flex-wrap items-center gap-5 sm:gap-7">
        @foreach ($modules as $module)
            @if (Route::has($module['route']))
                <a
                    href="{{ route($module['route']) }}"
                    class="text-sm font-semibold {{ request()->routeIs($module['pattern']) ? 'text-white' : 'text-accent-soft hover:text-white' }}"
                >
                    {{ $module['label'] }}
                </a>
            @else
                <span class="cursor-not-allowed text-sm font-semibold text-accent-soft/50" title="Próximamente">
                    {{ $module['label'] }}
                </span>
            @endif
        @endforeach
    </div>

    <div class="flex items-center gap-4 text-sm text-accent-soft">
        <span>{{ auth()->user()->username }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="font-semibold text-white hover:underline">Cerrar sesión</button>
        </form>
    </div>
</nav>
