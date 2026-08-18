@php
    $tabs = [
        ['label' => 'Roles', 'route' => 'security.role.index', 'pattern' => 'security.role.*'],
        ['label' => 'Permisos', 'route' => 'security.permission.index', 'pattern' => 'security.permission.*'],
        ['label' => 'Usuarios', 'route' => 'security.user.index', 'pattern' => 'security.user.*'],
    ];
@endphp

<div class="border-b border-border bg-surface-2">
    <div class="mx-auto flex max-w-5xl gap-1 overflow-x-auto px-4 sm:px-6">
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
