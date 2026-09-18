<script setup>
import { onMounted, ref } from 'vue';
import http from '../../utils/http';
import KpiCard from './components/KpiCard.vue';
import ReportsChart from './components/ReportsChart.vue';
import ReportSessions from './components/ReportSessions.vue';

const summary = ref(null);
const loadingSummary = ref(true);

const activeView = ref('chart'); // 'chart' | 'table'

const fields = ref([]);
const statuses = ref([]);
const filters = ref({ field_id: '', machinery_type_id: '', status_id: '', from: '', to: '' });

const chartData = ref(null);
const loadingChart = ref(true);

const sessions = ref([]);
const loadingSessions = ref(true);

const weeklyData = ref(null);
const loadingWeekly = ref(false);

function currentWeekValue() {
    const d = new Date();
    const utc = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const day = utc.getUTCDay() || 7;
    utc.setUTCDate(utc.getUTCDate() + 4 - day);
    const yearStart = new Date(Date.UTC(utc.getUTCFullYear(), 0, 1));
    const week = Math.ceil((((utc - yearStart) / 86400000) + 1) / 7);
    return `${utc.getUTCFullYear()}-W${String(week).padStart(2, '0')}`;
}
const weekInput = ref(currentWeekValue());

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
    const [fieldsRes, statusesRes] = await Promise.all([
        http.get('/configuracion/campos'),
        http.get('/configuracion/estados'),
    ]);
    fields.value = fieldsRes.data.data;
    statuses.value = statusesRes.data.data;
}

async function loadChart() {
    loadingChart.value = true;
    const { data } = await http.get('/dashboard/datos/grafica', { params: cleanFilters() });
    chartData.value = data;
    loadingChart.value = false;
}

async function loadSessions() {
    loadingSessions.value = true;
    const { data } = await http.get('/dashboard/datos/sesiones', { params: cleanFilters() });
    sessions.value = data;
    loadingSessions.value = false;
}

async function loadWeekly() {
    if (!weekInput.value) return;
    const [yearStr, weekStr] = weekInput.value.split('-W');
    loadingWeekly.value = true;
    const { data } = await http.get('/dashboard/datos/semanal', {
        params: { week: Number(weekStr), year: Number(yearStr), ...cleanFilters() },
    });
    weeklyData.value = data;
    loadingWeekly.value = false;
}

function applyFilters() {
    loadChart();
    loadSessions();
    if (activeView.value === 'weekly') loadWeekly();
}

function exportExcel() {
    const params = new URLSearchParams(cleanFilters());
    window.location.href = `/dashboard/datos/exportar?${params.toString()}`;
}


onMounted(async () => {
    // Pre-aplicar filtro de tipo desde URL (?machinery_type_id=X)
    const params = new URLSearchParams(window.location.search);
    const typeId = params.get('machinery_type_id');
    if (typeId) filters.value.machinery_type_id = Number(typeId);

    loadSummary();
    await loadOptions();
    loadChart();
    loadSessions();
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

        <div class="mt-10">
            <!-- Toggle Gráfico / Reporte + Exportar -->
            <div class="mb-5 flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold transition"
                    :class="activeView === 'chart' ? 'bg-accent border-accent text-white' : 'border-border text-text-muted hover:border-accent hover:text-accent'"
                    @click="activeView = 'chart'"
                >
                    Gráfico
                </button>
                <button
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold transition"
                    :class="activeView === 'table' ? 'bg-accent border-accent text-white' : 'border-border text-text-muted hover:border-accent hover:text-accent'"
                    @click="activeView = 'table'"
                >
                    Reporte
                </button>
                <button
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold transition"
                    :class="activeView === 'weekly' ? 'bg-accent border-accent text-white' : 'border-border text-text-muted hover:border-accent hover:text-accent'"
                    @click="activeView = 'weekly'; loadWeekly()"
                >
                    Semanal
                </button>

                <button
                    type="button"
                    title="Exportar a Excel"
                    class="ml-auto flex items-center gap-1.5 rounded-lg border border-[#1d6f42] bg-[#1d6f42] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#185c37]"
                    @click="exportExcel"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8.5 17l-1.8-3 1.8-3H10l-1.5 3L10 17H8.5zm3 0-1.5-3 1.5-3H13l-1.5 3 1.5 3h-1.5zm3.5 0h-1.5l1.5-3-1.5-3H15l1.5 3-1.5 3z"/>
                    </svg>
                    Excel
                </button>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Yarda</label>
                    <select v-model="filters.field_id" class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                        <option value="">Todas</option>
                        <option v-for="f in fields" :key="f.id" :value="f.id">{{ f.description }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Estado</label>
                    <select v-model="filters.status_id" class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                        <option value="">Todos</option>
                        <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.description }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Desde</label>
                    <input v-model="filters.from" type="date" class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-text-muted">Hasta</label>
                    <input v-model="filters.to" type="date" class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text" @change="applyFilters">
                </div>
            </div>

            <div v-if="activeView === 'chart'" class="mb-8 rounded-xl border border-border bg-surface p-5">
                <h2 class="mb-4 font-display text-base font-semibold text-text">Reportes por día</h2>
                <div v-if="loadingChart" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
                <ReportsChart v-else-if="chartData && chartData.labels.length" :chart-data="chartData" />
                <p v-else class="py-10 text-center text-sm text-text-muted">Sin datos en este rango.</p>
            </div>

            <div v-if="activeView === 'table'">
                <ReportSessions :sessions="sessions" :loading="loadingSessions" />
            </div>

            <div v-if="activeView === 'weekly'">
                <!-- Selector de semana -->
                <div class="mb-4 flex items-end gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Semana</label>
                        <input
                            v-model="weekInput"
                            type="week"
                            class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                            @change="loadWeekly"
                        >
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-surface p-5">
                    <h2 class="font-display text-base font-semibold text-text">Promedio diario por estado</h2>
                    <p v-if="weeklyData" class="mb-5 text-xs text-text-muted">Semana {{ weeklyData.week }}, {{ weeklyData.year }}</p>

                    <div v-if="loadingWeekly" class="py-10 text-center text-sm text-text-muted">Cargando…</div>

                    <div v-else-if="weeklyData && weeklyData.labels.length" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Tabla (izquierda) -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                        <th class="py-2 pr-4">Día</th>
                                        <th
                                            v-for="ds in weeklyData.datasets"
                                            :key="ds.label"
                                            class="py-2 pr-4 text-center"
                                            :class="{
                                                'text-good':     ds.class === 'good',
                                                'text-warn':     ds.class === 'warn',
                                                'text-critical': ds.class === 'critical',
                                            }"
                                        >
                                            {{ ds.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(label, i) in weeklyData.labels"
                                        :key="label"
                                        class="border-b border-border last:border-0"
                                    >
                                        <td class="py-2 pr-4 font-medium text-text">{{ label }}</td>
                                        <td
                                            v-for="ds in weeklyData.datasets"
                                            :key="ds.label"
                                            class="py-2 pr-4 text-center font-semibold"
                                            :class="{
                                                'text-good':     ds.class === 'good',
                                                'text-warn':     ds.class === 'warn',
                                                'text-critical': ds.class === 'critical',
                                                'text-text-muted': ds.class === 'neutral',
                                            }"
                                        >
                                            {{ Math.round(ds.data[i]) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Gráfico (derecha) -->
                        <ReportsChart :chart-data="weeklyData" />
                    </div>

                    <p v-else-if="weeklyData" class="py-10 text-center text-sm text-text-muted">Sin datos para esta semana.</p>
                </div>
            </div>
        </div>

    </main>
</template>
