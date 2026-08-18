<script setup>
import { onMounted, ref } from 'vue';
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
    machineId: {
        type: [String, Number],
        required: true,
    },
});

const router = useRouter();
const machineName = ref('');
const statuses = ref([]);
const loading = ref(true);
const saving = ref(false);
const submitted = ref(false);
const errors = ref({});

const statusId = ref('');
const observation = ref('');

async function load() {
    loading.value = true;
    const [machines, statusList] = await Promise.all([
        reportService.machines(props.fieldId, props.typeId),
        reportService.statuses(),
    ]);
    machineName.value = machines.find((m) => String(m.id) === String(props.machineId))?.description ?? '';
    statuses.value = statusList;
    loading.value = false;
}

onMounted(load);

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        await reportService.submit({
            machinery_id: props.machineId,
            status_id: statusId.value,
            observation: observation.value || null,
        });
        submitted.value = true;
    } catch (error) {
        if (error.response?.status === 422 && error.response.data.errors) {
            errors.value = error.response.data.errors;
        }
    } finally {
        saving.value = false;
    }
}

function reportAnother() {
    router.push({ name: 'report.yard' });
}
</script>

<template>
    <div>
        <StepHeader title="Reportar" subtitle="Paso 4 de 4" />
        <main class="mx-auto max-w-md px-4 py-4">
            <div v-if="submitted" class="rounded-xl border border-good/30 bg-good-soft px-4 py-6 text-center">
                <p class="font-display text-lg font-semibold text-good">Reporte registrado</p>
                <p class="mt-1 text-sm text-text-muted">{{ machineName }}</p>
                <button
                    type="button"
                    class="mt-4 rounded-md bg-accent px-4 py-2.5 text-sm font-semibold text-on-accent hover:bg-accent-strong"
                    @click="reportAnother"
                >
                    Reportar otra máquina
                </button>
            </div>

            <template v-else>
                <p class="mb-3 text-sm text-text-muted">{{ machineName }}</p>

                <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
                <form v-else class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Estado</label>
                        <div class="space-y-2">
                            <label
                                v-for="status in statuses"
                                :key="status.id"
                                class="flex items-center gap-3 rounded-xl border px-4 py-3"
                                :class="String(statusId) === String(status.id) ? 'border-accent bg-accent-soft' : 'border-border bg-surface'"
                            >
                                <input v-model="statusId" type="radio" :value="status.id" class="h-4 w-4 accent-accent">
                                <span class="text-sm font-medium text-text">{{ status.description }}</span>
                            </label>
                        </div>
                        <p v-if="errors.status_id" class="mt-1 text-xs text-critical">{{ errors.status_id[0] }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Observación (opcional)</label>
                        <textarea
                            v-model="observation"
                            rows="3"
                            maxlength="250"
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        ></textarea>
                        <p v-if="errors.observation" class="mt-1 text-xs text-critical">{{ errors.observation[0] }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="saving || !statusId"
                        class="w-full rounded-md bg-accent px-4 py-3 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
                    >
                        {{ saving ? 'Guardando…' : 'Registrar' }}
                    </button>
                </form>
            </template>
        </main>
    </div>
</template>
