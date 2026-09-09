<script setup>
defineProps({
    sessions: { type: Array, required: true },
    loading:  { type: Boolean, default: false },
});

function formatDateTime(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('es-GT', { dateStyle: 'medium', timeStyle: 'short' });
}

const badgeClasses = {
    good:     'bg-good-soft text-good',
    warn:     'bg-warn-soft text-warn',
    critical: 'bg-critical-soft text-critical',
    neutral:  'bg-surface-2 text-text-muted',
};
</script>

<template>
    <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>

    <div v-else-if="!sessions.length" class="py-10 text-center text-sm text-text-muted">
        Sin registros en este rango de fechas.
    </div>

    <div v-else class="space-y-5">
        <div
            v-for="(session, si) in sessions"
            :key="si"
            class="overflow-hidden rounded-xl border border-border bg-surface"
        >
            <!-- Cabecera de sesión -->
            <div class="flex items-center justify-between border-b border-border bg-surface-2 px-4 py-3">
                <span class="font-display text-sm font-semibold text-text">
                    {{ session.field }} &mdash; {{ formatDateTime(session.session_time) }}
                </span>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="text-good">&#x1F7E2; {{ session.summary.good }}</span>
                    <span class="text-warn">&#x26A0;&#xFE0F; {{ session.summary.warn }}</span>
                    <span class="text-critical">&#x1F534; {{ session.summary.critical }}</span>
                </div>
            </div>

            <!-- Tabla de máquinas -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[480px] text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            <th class="w-10 px-4 py-2.5">#</th>
                            <th class="px-4 py-2.5">Máquina</th>
                            <th class="px-4 py-2.5">Estado</th>
                            <th class="px-4 py-2.5">Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(m, mi) in session.machines"
                            :key="mi"
                            class="border-b border-border last:border-0"
                        >
                            <td class="px-4 py-2.5 text-text-muted">{{ mi + 1 }}</td>
                            <td class="px-4 py-2.5 font-medium text-text">{{ m.machinery }}</td>
                            <td class="px-4 py-2.5">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="badgeClasses[m.status_class] || badgeClasses.neutral"
                                >
                                    {{ m.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-text-muted">{{ m.observation || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
