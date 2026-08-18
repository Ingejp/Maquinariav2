<script setup>
import NavBar from '../../components/common/NavBar.vue';
import EmptyState from '../../components/common/EmptyState.vue';

defineProps({
    username: {
        type: String,
        default: '',
    },
});

const modules = [
    { key: 'configuracion', label: 'Configuración', href: '#' },
    { key: 'reportar', label: 'Reportar', href: '#' },
    { key: 'dashboard', label: 'Dashboard', href: '#' },
    { key: 'seguridad', label: 'Seguridad', href: '#' },
];
</script>

<template>
    <div>
        <NavBar :modules="modules" :username="username" />
        <main class="mx-auto max-w-3xl px-6 py-16">
            <EmptyState
                title="Maquinaria — Fase 1 completa"
                description="Autenticación por usuario y RBAC (Spatie) listos. Los módulos reales se construyen en las fases siguientes."
            />
        </main>
    </div>
</template>
