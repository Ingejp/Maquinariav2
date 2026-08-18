<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    record: {
        type: Object,
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});
const emit = defineEmits(['save', 'cancel']);

const description = ref('');

watch(
    () => props.record,
    (record) => {
        description.value = record?.description ?? '';
    },
    { immediate: true },
);

function submit() {
    emit('save', { description: description.value });
}
</script>

<template>
    <div class="rounded-xl border border-border bg-surface p-5">
        <h3 class="font-display text-base font-semibold text-text">
            {{ record ? `Editar ${label}` : `Nueva ${label}` }}
        </h3>
        <form class="mt-4 space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-xs font-semibold text-text-muted">Nombre</label>
                <input
                    v-model="description"
                    type="text"
                    maxlength="25"
                    required
                    class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                >
                <p v-if="errors.description" class="mt-1 text-xs text-critical">{{ errors.description[0] }}</p>
            </div>
            <div class="flex gap-2">
                <button
                    type="submit"
                    :disabled="saving"
                    class="flex-1 rounded-md bg-accent px-4 py-2.5 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
                >
                    {{ saving ? 'Guardando…' : 'Guardar' }}
                </button>
                <button
                    v-if="record"
                    type="button"
                    class="rounded-md border border-border px-4 py-2.5 text-sm font-semibold text-text hover:bg-surface-2"
                    @click="$emit('cancel')"
                >
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</template>
