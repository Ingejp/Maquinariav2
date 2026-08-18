<script setup>
defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="active ? 'bg-good-soft text-good' : 'bg-critical-soft text-critical'"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="active ? 'bg-good' : 'bg-critical'"></span>
        {{ active ? 'Activo' : 'Deshabilitado' }}
    </span>
</template>
