@php
    $modules = [
        ['label' => 'Configuración', 'route' => 'catalogs.field.index', 'pattern' => 'catalogs.*'],
        ['label' => 'Reportar', 'route' => 'report.index', 'pattern' => 'report.*'],
        ['label' => 'Dashboard', 'route' => 'dashboard.index', 'pattern' => 'dashboard.*'],
        ['label' => 'Seguridad', 'route' => 'security.index', 'pattern' => 'security.*'],
    ];
@endphp

<nav class="bg-accent">
    {{-- Barra principal --}}
    <div class="flex items-center justify-between px-4 py-3 sm:px-6">

        {{-- Desktop: links de módulos --}}
        <div class="hidden items-center gap-7 sm:flex">
            @foreach ($modules as $module)
                @if (Route::has($module['route']))
                    <a
                        href="{{ route($module['route']) }}"
                        class="text-sm font-semibold {{ request()->routeIs($module['pattern']) ? 'text-white' : 'text-blue-200 hover:text-white' }}"
                    >
                        {{ $module['label'] }}
                    </a>
                @else
                    <span class="cursor-not-allowed text-sm font-semibold text-blue-200/50">{{ $module['label'] }}</span>
                @endif
            @endforeach
        </div>

        {{-- Mobile: nombre de usuario (referencia visual) --}}
        <span class="text-sm font-semibold text-white sm:hidden">{{ auth()->user()->username }}</span>

        {{-- Desktop: usuario + cerrar sesión --}}
        <div class="hidden items-center gap-4 text-sm sm:flex">
            <span class="text-blue-200">{{ auth()->user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="font-semibold text-white hover:underline">Cerrar sesión</button>
            </form>
        </div>

        {{-- Mobile: botón hamburguesa --}}
        <button
            type="button"
            aria-label="Abrir menú"
            onclick="var m=document.getElementById('nav-mobile');m.classList.toggle('hidden');"
            class="rounded-md p-2 text-white hover:bg-white/10 sm:hidden"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    {{-- Menú móvil desplegable --}}
    <div id="nav-mobile" class="hidden border-t border-white/20 sm:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($modules as $module)
                @if (Route::has($module['route']))
                    <a
                        href="{{ route($module['route']) }}"
                        class="block rounded-md px-3 py-2.5 text-sm font-semibold {{ request()->routeIs($module['pattern']) ? 'bg-white/15 text-white' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}"
                    >
                        {{ $module['label'] }}
                    </a>
                @else
                    <span class="block rounded-md px-3 py-2.5 text-sm font-semibold text-blue-200/50">{{ $module['label'] }}</span>
                @endif
            @endforeach
        </div>
        <div class="border-t border-white/20 px-4 py-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-md px-3 py-2.5 text-left text-sm font-semibold text-white hover:bg-white/10">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>
