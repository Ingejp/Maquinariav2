<script setup>
import { useCatalogPage } from '../../../utils/useCatalogPage';
import CatalogTable from '../../../components/configuration/CatalogTable.vue';
import CatalogFormPanel from '../../../components/configuration/CatalogFormPanel.vue';
import StatusBadge from '../../../components/configuration/StatusBadge.vue';
import ConfirmDialog from '../../../components/common/ConfirmDialog.vue';

const {
    items, loading, saving, errors, editing, confirmingDelete, banner, formKey,
    startEdit, cancelEdit, save, toggleStatus, askDelete, cancelDelete, confirmDelete,
} = useCatalogPage('/configuracion/estados');
</script>

<template>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Configuración</p>
            <h1 class="font-display text-2xl font-semibold text-text">Estados</h1>
            <p class="mt-1 text-sm text-text-muted">Los valores que un operario puede elegir al reportar el estado de una máquina.</p>
        </div>

        <div
            v-if="banner"
            class="mb-4 rounded-md px-4 py-2.5 text-sm"
            :class="banner.type === 'error' ? 'bg-critical-soft text-critical' : 'bg-good-soft text-good'"
        >
            {{ banner.message }}
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">
            <CatalogTable :items="items" :loading="loading" empty-message="No hay estados registrados." @edit="startEdit" @toggle="toggleStatus" @delete="askDelete">
                <template #head>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Estado</th>
                </template>
                <template #row="{ item }">
                    <td class="px-4 py-3 font-medium text-text">{{ item.description }}</td>
                    <td class="px-4 py-3"><StatusBadge :active="item.active" /></td>
                </template>
            </CatalogTable>

            <CatalogFormPanel
                :key="formKey"
                label="estado"
                :record="editing"
                :saving="saving"
                :errors="errors"
                @save="save"
                @cancel="cancelEdit"
            />
        </div>

        <ConfirmDialog
            :open="!!confirmingDelete"
            title="Eliminar estado"
            :message="`¿Eliminar “${confirmingDelete?.description}”? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </main>
</template>
