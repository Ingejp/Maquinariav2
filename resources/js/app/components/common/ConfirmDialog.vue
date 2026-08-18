<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '¿Confirmar?',
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Eliminar',
    },
});
defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" @click.self="$emit('cancel')">
        <div class="w-full max-w-sm rounded-xl bg-surface p-5 shadow-lg">
            <h3 class="font-display text-base font-semibold text-text">{{ title }}</h3>
            <p class="mt-2 text-sm text-text-muted">{{ message }}</p>
            <div class="mt-5 flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-md border border-border px-4 py-2 text-sm font-semibold text-text hover:bg-surface-2"
                    @click="$emit('cancel')"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-md bg-critical px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
                    @click="$emit('confirm')"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
