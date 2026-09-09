<x-layouts.app title="Dashboard">
    <main class="mx-auto max-w-2xl px-4 py-10 sm:px-6">

        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Dashboard</p>
            <h1 class="font-display text-2xl font-semibold text-text">¿Qué tipo de maquinaria?</h1>
            <p class="mt-1 text-sm text-text-muted">Selecciona el tipo para ver el estado de la flota.</p>
        </div>

        @if ($types->isEmpty())
            <p class="text-sm text-text-muted">No hay tipos de maquinaria activos.</p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:gap-5">
                @foreach ($types as $type)
                    <a
                        href="{{ route('dashboard.index', ['machinery_type_id' => $type->id]) }}"
                        class="group flex flex-col rounded-2xl border border-border bg-surface p-5 transition hover:border-accent hover:shadow-sm"
                    >
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-accent-soft text-accent transition group-hover:bg-accent group-hover:text-white">
                            {{-- Ícono de equipo --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-2h6l2 2zm0 0l2-2h3l2 2V9a1 1 0 00-1-1h-4a1 1 0 00-1 1v7z" />
                            </svg>
                        </div>

                        <p class="font-display font-semibold text-text">{{ $type->description }}</p>
                        <p class="mt-1 text-xs text-text-muted">
                            {{ $type->machinery_count }} máquina{{ $type->machinery_count !== 1 ? 's' : '' }} activa{{ $type->machinery_count !== 1 ? 's' : '' }}
                        </p>

                        <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-accent group-hover:text-accent-strong">
                            Ver estado
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </main>
</x-layouts.app>
