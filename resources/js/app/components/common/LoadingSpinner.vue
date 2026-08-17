<script setup>
defineProps({
    label: {
        type: String,
        default: 'Cargando...',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-500">
        <span
            class="h-8 w-8 animate-spin rounded-full border-2 border-slate-300 border-t-slate-700"
            role="status"
            aria-hidden="true"
        ></span>
        <p class="text-sm">{{ label }}</p>
    </div>
</template>
