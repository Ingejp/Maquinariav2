<script setup>
defineProps({
    title: {
        type: String,
        default: 'Sin datos',
    },
    description: {
        type: String,
        default: 'No hay información para mostrar todavía.',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-1 py-12 text-center text-slate-500">
        <p class="text-sm font-medium text-slate-700">{{ title }}</p>
        <p class="text-sm">{{ description }}</p>
        <slot />
    </div>
</template>
