<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import reportService from '../../../services/reportService';
import StepHeader from '../components/StepHeader.vue';

const props = defineProps({
    fieldId: { type: [String, Number], required: true },
    typeId: { type: [String, Number], required: true },
});

const router = useRouter();
const typeName = ref('');
const machines = ref([]);
const statuses = ref([]);
const loading = ref(true);
const saving = ref(false);
const submitted = ref(false);
const globalError = ref('');

// Estado del formulario por máquina: { [machineId]: { status_id, observation } }
const forms = ref({});

async function load() {
    loading.value = true;
    const [types, machineList, statusList] = await Promise.all([
        reportService.machineryTypes(props.fieldId),
        reportService.machines(props.fieldId, props.typeId),
        reportService.statuses(),
    ]);
    typeName.value = types.find((t) => String(t.id) === String(props.typeId))?.description ?? '';
    machines.value = machineList;
    statuses.value = statusList;
    forms.value = Object.fromEntries(machineList.map((m) => [m.id, { status_id: m.last_report?.status_id ?? '', observation: '' }]));
    loading.value = false;
}

onMounted(load);
watch(() => [props.fieldId, props.typeId], load);

const selectedCount = computed(() => Object.values(forms.value).filter((f) => f.status_id !== '').length);

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('es-GT', { dateStyle: 'short', timeStyle: 'short' });
}

// Clases completas para que Tailwind las detecte en el build
const statusColors = {
    good:     { inactive: 'border-good text-good',         active: 'bg-good border-good text-white' },
    warn:     { inactive: 'border-warn text-warn',         active: 'bg-warn border-warn text-white' },
    critical: { inactive: 'border-critical text-critical', active: 'bg-critical border-critical text-white' },
    neutral:  { inactive: 'border-border text-text-muted', active: 'bg-text-muted border-text-muted text-white' },
};

const dotColors = {
    good: 'bg-good', warn: 'bg-warn', critical: 'bg-critical', neutral: 'bg-text-muted',
};

function btnClass(status, machineId) {
    const colors = statusColors[status.class] ?? statusColors.neutral;
    const isSelected = String(forms.value[machineId]?.status_id) === String(status.id);
    return isSelected ? colors.active : colors.inactive;
}

async function saveAll() {
    const toSave = machines.value.filter((m) => forms.value[m.id]?.status_id !== '');
    if (!toSave.length) return;
    saving.value = true;
    globalError.value = '';
    try {
        await Promise.all(
            toSave.map((m) =>
                reportService.submit({
                    machinery_id: m.id,
                    status_id: forms.value[m.id].status_id,
                    observation: forms.value[m.id].observation || null,
                }),
            ),
        );
        submitted.value = true;
    } catch {
        globalError.value = 'No se pudo guardar uno o más reportes. Intenta de nuevo.';
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
        <StepHeader title="Reportar" :subtitle="typeName" />
        <main class="mx-auto max-w-md px-4 py-4">

            <!-- Estado de éxito -->
            <div v-if="submitted" class="rounded-xl border border-good/30 bg-good-soft px-4 py-8 text-center">
                <p class="font-display text-lg font-semibold text-good">Reportes guardados</p>
                <p class="mt-1 text-sm text-text-muted">
                    {{ selectedCount }} máquina{{ selectedCount !== 1 ? 's' : '' }} reportada{{ selectedCount !== 1 ? 's' : '' }} correctamente.
                </p>
                <button
                    type="button"
                    class="mt-4 rounded-md bg-accent px-4 py-2.5 text-sm font-semibold text-on-accent hover:bg-accent-strong"
                    @click="reportAnother"
                >
                    Reportar en otra yarda
                </button>
            </div>

            <template v-else>
                <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
                <div v-else-if="!machines.length" class="py-10 text-center text-sm text-text-muted">No hay máquinas activas de este tipo.</div>

                <div v-else class="space-y-4 pb-28">
                    <div
                        v-for="machine in machines"
                        :key="machine.id"
                        class="rounded-xl border border-border bg-surface p-4"
                    >
                        <!-- Encabezado de la card -->
                        <div class="mb-4 flex items-center gap-3">
                            <!-- Ícono de equipo (placeholder — se reemplaza cuando el catálogo tenga imagen) -->
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-surface-2">
                                <svg class="h-8 w-8 text-text-muted" viewBox="0 0 48 48" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="6" y="34" width="28" height="5" rx="2" />
                                    <rect x="8" y="22" width="5" height="12" rx="1.5" />
                                    <rect x="8" y="22" width="20" height="4" rx="1.5" />
                                    <rect x="24" y="22" width="5" height="12" rx="1.5" />
                                    <circle cx="11" cy="41" r="3.5" fill="none" stroke="currentColor" stroke-width="2" />
                                    <circle cx="31" cy="41" r="3.5" fill="none" stroke="currentColor" stroke-width="2" />
                                    <line x1="18" y1="22" x2="12" y2="11" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    <line x1="18" y1="22" x2="24" y2="11" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    <rect x="36" y="18" width="6" height="21" rx="2" />
                                    <rect x="29" y="18" width="13" height="4" rx="1.5" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="font-display font-semibold text-text">{{ machine.description }}</p>
                                <p v-if="machine.last_report" class="mt-0.5 flex items-center gap-1.5 text-xs text-text-muted">
                                    <span
                                        class="inline-block h-1.5 w-1.5 shrink-0 rounded-full"
                                        :class="dotColors[machine.last_report.status_class] ?? dotColors.neutral"
                                    ></span>
                                    {{ machine.last_report.status }} · {{ formatDate(machine.last_report.reported_at) }}
                                </p>
                                <p v-else class="mt-0.5 text-xs text-text-muted">Sin reportes previos</p>
                            </div>
                        </div>

                        <!-- Selector de estado (3 botones visibles) -->
                        <div class="mb-3 flex gap-1.5">
                            <button
                                v-for="status in statuses"
                                :key="status.id"
                                type="button"
                                class="flex-1 rounded-lg border py-2.5 text-center text-[11px] font-semibold leading-tight transition-colors"
                                :class="btnClass(status, machine.id)"
                                @click="forms[machine.id].status_id = status.id"
                            >
                                {{ status.description }}
                            </button>
                        </div>

                        <!-- Observación -->
                        <textarea
                            v-model="forms[machine.id].observation"
                            rows="2"
                            maxlength="250"
                            placeholder="Observación (opcional)"
                            class="w-full resize-none rounded-md border border-border px-3 py-2 text-sm text-text placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        ></textarea>
                    </div>
                </div>

                <!-- Barra sticky de guardado -->
                <div v-if="!loading && machines.length" class="fixed inset-x-0 bottom-0 border-t border-border bg-surface px-4 py-3 shadow-lg">
                    <div class="mx-auto max-w-md">
                        <p v-if="globalError" class="mb-2 text-center text-xs text-critical">{{ globalError }}</p>
                        <p class="mb-2 text-center text-xs text-text-muted">
                            {{ selectedCount }} de {{ machines.length }} máquina{{ machines.length !== 1 ? 's' : '' }} con estado seleccionado
                        </p>
                        <button
                            type="button"
                            :disabled="saving || selectedCount === 0"
                            class="w-full rounded-md bg-accent px-4 py-3 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
                            @click="saveAll"
                        >
                            {{ saving ? 'Guardando…' : `Guardar ${selectedCount > 0 ? selectedCount : ''} reporte${selectedCount !== 1 ? 's' : ''}` }}
                        </button>
                    </div>
                </div>
            </template>
        </main>
    </div>
</template>
