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
    typeId: {
        type: [String, Number],
        required: true,
    },
});

const router = useRouter();
const typeName = ref('');
const machines = ref([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    const [types, machineList] = await Promise.all([
        reportService.machineryTypes(props.fieldId),
        reportService.machines(props.fieldId, props.typeId),
    ]);
    typeName.value = types.find((t) => String(t.id) === String(props.typeId))?.description ?? '';
    machines.value = machineList;
    loading.value = false;
}

onMounted(load);
watch(() => [props.fieldId, props.typeId], load);

function select(machine) {
    router.push({
        name: 'report.register',
        params: { fieldId: props.fieldId, typeId: props.typeId, machineId: machine.id },
    });
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('es-GT', { dateStyle: 'short', timeStyle: 'short' });
}
</script>

<template>
    <div>
        <StepHeader title="Reportar" :subtitle="`Paso 3 de 4 · ${typeName}`" />
        <main class="mx-auto max-w-md px-4 py-4">
            <p class="mb-3 text-sm text-text-muted">Selecciona una máquina</p>

            <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
            <div v-else-if="!machines.length" class="py-10 text-center text-sm text-text-muted">No hay máquinas activas de este tipo.</div>
            <div v-else class="space-y-2">
                <button
                    v-for="machine in machines"
                    :key="machine.id"
                    type="button"
                    class="flex w-full flex-col items-start rounded-xl border border-border bg-surface px-4 py-4 text-left hover:border-accent"
                    @click="select(machine)"
                >
                    <span class="font-medium text-text">{{ machine.description }}</span>
                    <span v-if="machine.last_report" class="mt-1 text-xs text-text-muted">
                        Último: {{ machine.last_report.status }} · {{ formatDate(machine.last_report.reported_at) }}
                    </span>
                    <span v-else class="mt-1 text-xs text-text-muted">Sin reportes previos</span>
                </button>
            </div>
        </main>
    </div>
</template>
