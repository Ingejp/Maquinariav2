<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import reportService from '../../../services/reportService';
import StepHeader from '../components/StepHeader.vue';

const router = useRouter();
const fields = ref([]);
const loading = ref(true);

onMounted(async () => {
    fields.value = await reportService.fields();
    loading.value = false;
});

function select(field) {
    router.push({ name: 'report.type', params: { fieldId: field.id } });
}
</script>

<template>
    <div>
        <StepHeader title="Reportar" subtitle="Paso 1 de 4" />
        <main class="mx-auto max-w-md px-4 py-4">
            <p class="mb-3 text-sm text-text-muted">Selecciona una yarda</p>

            <div v-if="loading" class="py-10 text-center text-sm text-text-muted">Cargando…</div>
            <div v-else-if="!fields.length" class="py-10 text-center text-sm text-text-muted">No hay yardas activas.</div>
            <div v-else class="space-y-2">
                <button
                    v-for="field in fields"
                    :key="field.id"
                    type="button"
                    class="flex w-full items-center justify-between rounded-xl border border-border bg-surface px-4 py-4 text-left hover:border-accent"
                    @click="select(field)"
                >
                    <span class="font-medium text-text">{{ field.description }}</span>
                    <span class="text-xs text-text-muted">{{ field.machinery_count }} máquinas</span>
                </button>
            </div>
        </main>
    </div>
</template>
