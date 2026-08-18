@php
    $tabs = [
        ['label' => 'Yardas', 'route' => 'catalogs.field.index', 'pattern' => 'catalogs.field.*'],
        ['label' => 'Estados', 'route' => 'catalogs.status.index', 'pattern' => 'catalogs.status.*'],
        ['label' => 'Tipos de maquinaria', 'route' => 'catalogs.machinery-type.index', 'pattern' => 'catalogs.machinery-type.*'],
        ['label' => 'Maquinaria', 'route' => 'catalogs.machinery.index', 'pattern' => 'catalogs.machinery.*'],
    ];
@endphp

<div class="border-b border-border bg-surface-2">
    <div class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-4 sm:px-6">
        @foreach ($tabs as $tab)
            <a
                href="{{ route($tab['route']) }}"
                class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-medium {{ request()->routeIs($tab['pattern']) ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-text' }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
