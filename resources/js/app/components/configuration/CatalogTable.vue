<script setup>
defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: 'Sin registros.',
    },
});
defineEmits(['edit', 'toggle', 'delete']);
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-border bg-surface">
        <table class="w-full min-w-[560px] text-sm">
            <thead>
                <tr class="border-b border-border bg-surface-2 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                    <slot name="head" />
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td colspan="99" class="px-4 py-10 text-center text-text-muted">Cargando…</td>
                </tr>
                <tr v-else-if="!items.length">
                    <td colspan="99" class="px-4 py-10 text-center text-text-muted">{{ emptyMessage }}</td>
                </tr>
                <tr v-for="item in items" v-else :key="item.id" class="border-b border-border last:border-0">
                    <slot name="row" :item="item" />
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-3">
                            <button type="button" class="text-sm font-medium text-accent hover:text-accent-strong" @click="$emit('edit', item)">
                                Editar
                            </button>
                            <button type="button" class="text-sm font-medium text-text-muted hover:text-text" @click="$emit('toggle', item)">
                                {{ item.active ? 'Desactivar' : 'Activar' }}
                            </button>
                            <button type="button" class="text-sm font-medium text-critical hover:opacity-80" @click="$emit('delete', item)">
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
