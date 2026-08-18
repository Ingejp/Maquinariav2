<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import reportService from '../../../services/reportService';
import StepHeader from '../components/StepHeader.vue';

const props = defineProps({
    fieldId: {
        type: [String, Number],
        required: true,
    },
});

const router = useRouter();
const fieldName = ref('');
const types = ref([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    const [fields, machineryTypes] = await Promise.all([
        reportService.fields(),
        reportService.machineryTypes(props.fieldId),
    ]);
    fieldName.value = fields.find((f) => String(f.id) === String(props.fieldId))?.description ?? '';
    types.value = machineryTypes;
    loading.value = false;
}

onMounted(load);
watch(() => props.fieldId, load);

function select(type) {
    router.push({ name: 'report.machine', params: { fieldId: props.fieldId, typeId: type.id } });
}
</script>

<template>
    <div>
        <StepHeader title="Reportar" :subtitle="`Paso 2 de 4 · ${fieldName}`" />
        <main class="mx-auto max-w-md px-4 py-4">
            <p class="mb-3 text-sm text-text-muted">Selecciona un tipo de maquinaria</p>

            <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
            <div v-else-if="!types.length" class="py-10 text-center text-sm text-text-muted">Esta yarda no tiene máquinas activas.</div>
            <div v-else class="space-y-2">
                <button
                    v-for="type in types"
                    :key="type.id"
                    type="button"
                    class="w-full rounded-xl border border-border bg-surface px-4 py-4 text-left font-medium text-text hover:border-accent"
                    @click="select(type)"
                >
                    {{ type.description }}
                </button>
            </div>
        </main>
    </div>
</template>
