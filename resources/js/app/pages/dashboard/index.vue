<script setup>
import { onMounted, ref } from 'vue';
import http from '../../utils/http';
import KpiCard from './components/KpiCard.vue';
import ReportsChart from './components/ReportsChart.vue';

const summary = ref(null);
const loadingSummary = ref(true);

const fields = ref([]);
const machineryTypes = ref([]);
const statuses = ref([]);
const filters = ref({ field_id: '', machinery_type_id: '', status_id: '', from: '', to: '' });

const chartData = ref(null);
const loadingChart = ref(true);

const reports = ref([]);
const reportsMeta = ref({ current_page: 1, last_page: 1, total: 0 });
const loadingReports = ref(true);

function cleanFilters() {
    const out = {};
    if (filters.value.field_id) out.field_id = filters.value.field_id;
    if (filters.value.machinery_type_id) out.machinery_type_id = filters.value.machinery_type_id;
    if (filters.value.status_id) out.status_id = filters.value.status_id;
    if (filters.value.from) out.from = filters.value.from;
    if (filters.value.to) out.to = filters.value.to;
    return out;
}

async function loadSummary() {
    loadingSummary.value = true;
    const { data } = await http.get('/dashboard/datos/resumen');
    summary.value = data;
    loadingSummary.value = false;
}

async function loadOptions() {
    const [fieldsRes, typesRes, statusesRes] = await Promise.all([
        http.get('/configuracion/campos'),
        http.get('/configuracion/tipos-maquinaria'),
        http.get('/configuracion/estados'),
    ]);
    fields.value = fieldsRes.data.data;
    machineryTypes.value = typesRes.data.data;
    statuses.value = statusesRes.data.data;
}

async function loadChart() {
    loadingChart.value = true;
    const { data } = await http.get('/dashboard/datos/grafica', { params: cleanFilters() });
    chartData.value = data;
    loadingChart.value = false;
}

async function loadReports(page = 1) {
    loadingReports.value = true;
    const { data } = await http.get('/dashboard/datos/reportes', { params: { ...cleanFilters(), page } });
    reports.value = data.data;
    reportsMeta.value = { current_page: data.current_page, last_page: data.last_page, total: data.total };
    loadingReports.value = false;
}

function applyFilters() {
    loadChart();
    loadReports(1);
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('es-GT', { dateStyle: 'short', timeStyle: 'short' });
}

const statusBadgeClasses = {
    good: 'bg-good-soft text-good',
    warn: 'bg-warn-soft text-warn',
    critical: 'bg-critical-soft text-critical',
    neutral: 'bg-surface-2 text-text-muted',
};
const statusDotClasses = {
    good: 'bg-good',
    warn: 'bg-warn',
    critical: 'bg-critical',
    neutral: 'bg-text-muted',
};

onMounted(async () => {
    // Pre-aplicar filtro de tipo desde URL (?machinery_type_id=X)
    const params = new URLSearchParams(window.location.search);
    const typeId = params.get('machinery_type_id');
    if (typeId) filters.value.machinery_type_id = Number(typeId);

    loadSummary();
    await loadOptions();
    loadChart();
    loadReports();
});
</script>

<template>
    <main class="px-4 py-6 sm:px-6">
        <div class="mb-6">
            <a href="/dashboard" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-text-muted hover:text-accent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Cambiar tipo
            </a>
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Dashboard</p>
            <h1 class="font-display text-2xl font-semibold text-text">Estado de la flota</h1>
        </div>

        <div v-if="loadingSummary" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
        <div v-else-if="summary" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <KpiCard label="Máquinas activas" :value="summary.active_machines" />
            <KpiCard label="Reportes hoy" :value="summary.reports_today" />
            <KpiCard
                v-for="item in summary.by_status"
                :key="item.label"
                :label="item.label"
                :value="item.count"
                :tone="item.class"
            />
        </div>

        <div class="mt-10 hidden lg:block">
            <div class="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Yarda</label>
                    <select v-model="filters.field_id" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                        <option value="">Todas</option>
                        <option v-for="f in fields" :key="f.id" :value="f.id">{{ f.description }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Tipo</label>
                    <select v-model="filters.machinery_type_id" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                        <option value="">Todos</option>
                        <option v-for="t in machineryTypes" :key="t.id" :value="t.id">{{ t.description }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Estado</label>
                    <select v-model="filters.status_id" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                        <option value="">Todos</option>
                        <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.description }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Desde</label>
                    <input v-model="filters.from" type="date" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Hasta</label>
                    <input v-model="filters.to" type="date" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                </div>
            </div>

            <div class="mb-8 rounded-xl border border-border bg-surface p-5">
                <h2 class="mb-4 font-display text-base font-semibold text-text">Reportes por día</h2>
                <div v-if="loadingChart" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
                <ReportsChart v-else-if="chartData && chartData.labels.length" :chart-data="chartData" />
                <p v-else class="py-10 text-center text-sm text-text-muted">Sin datos en este rango.</p>
            </div>

            <div class="rounded-xl border border-border bg-surface">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead>
                            <tr class="border-b border-border bg-surface-2 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Máquina</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Yarda</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3">Observación</th>
                                <th class="px-4 py-3">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingReports">
                                <td colspan="7" class="px-4 py-10 text-center text-text-muted">Cargando…</td>
                            </tr>
                            <tr v-else-if="!reports.length">
                                <td colspan="7" class="px-4 py-10 text-center text-text-muted">Sin reportes en este rango.</td>
                            </tr>
                            <tr v-for="r in reports" v-else :key="r.id" class="border-b border-border last:border-0">
                                <td class="px-4 py-3 text-text-muted">{{ formatDate(r.created_at) }}</td>
                                <td class="px-4 py-3 font-medium text-text">{{ r.machinery }}</td>
                                <td class="px-4 py-3 text-text-muted">{{ r.machinery_type }}</td>
                                <td class="px-4 py-3 text-text-muted">{{ r.field }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="statusBadgeClasses[r.status_class] || statusBadgeClasses.neutral"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClasses[r.status_class] || statusDotClasses.neutral"></span>
                                        {{ r.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-text-muted">{{ r.observation || '—' }}</td>
                                <td class="px-4 py-3 text-text-muted">{{ r.user }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="reportsMeta.last_page > 1" class="flex items-center justify-between border-t border-border px-4 py-3 text-sm text-text-muted">
                    <span>Página {{ reportsMeta.current_page }} de {{ reportsMeta.last_page }} · {{ reportsMeta.total }} reportes</span>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            :disabled="reportsMeta.current_page <= 1"
                            class="rounded-md border border-border px-3 py-1.5 disabled:opacity-40"
                            @click="loadReports(reportsMeta.current_page - 1)"
                        >
                            Anterior
                        </button>
                        <button
                            type="button"
                            :disabled="reportsMeta.current_page >= reportsMeta.last_page"
                            class="rounded-md border border-border px-3 py-1.5 disabled:opacity-40"
                            @click="loadReports(reportsMeta.current_page + 1)"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 rounded-xl border border-border bg-surface-2 p-6 text-center lg:hidden">
            <p class="text-sm text-text-muted">Las gráficas y el detalle de reportes están disponibles en pantallas más grandes.</p>
        </div>
    </main>
</template>
