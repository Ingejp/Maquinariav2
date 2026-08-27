<script setup>
import { onMounted, ref, watch } from 'vue';
import http from '../../../utils/http';
import createCatalogService from '../../../services/catalogService';
import CatalogTable from '../../../components/configuration/CatalogTable.vue';
import StatusBadge from '../../../components/configuration/StatusBadge.vue';
import ConfirmDialog from '../../../components/common/ConfirmDialog.vue';

const service = createCatalogService('/configuracion/maquinaria');

const items = ref([]);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});
const editing = ref(null);
const confirmingDelete = ref(null);
const banner = ref(null);

const fields = ref([]);
const machineryTypes = ref([]);
const filters = ref({ field_id: '', machinery_type_id: '' });

const emptyForm = () => ({ description: '', machinery_type_id: '', field_id: '' });
const form = ref(emptyForm());

async function fetchOptions() {
    const { data } = await http.get('/configuracion/maquinaria/opciones');
    fields.value = data.fields;
    machineryTypes.value = data.machinery_types;
}

async function fetchItems() {
    loading.value = true;
    try {
        items.value = await service.list({
            field_id: filters.value.field_id || undefined,
            machinery_type_id: filters.value.machinery_type_id || undefined,
        });
    } catch {
        banner.value = { type: 'error', message: 'No se pudo cargar el listado.' };
    } finally {
        loading.value = false;
    }
}

function startEdit(item) {
    editing.value = item;
    form.value = {
        description: item.description,
        machinery_type_id: item.machinery_type.id,
        field_id: item.field.id,
    };
    errors.value = {};
}

function cancelEdit() {
    editing.value = null;
    form.value = emptyForm();
    errors.value = {};
}

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        if (editing.value) {
            const updated = await service.update(editing.value.id, form.value);
            const idx = items.value.findIndex((i) => i.id === updated.id);
            if (idx !== -1) items.value.splice(idx, 1, updated);
        } else {
            const created = await service.create(form.value);
            items.value.push(created);
            items.value.sort((a, b) => a.description.localeCompare(b.description));
        }
        banner.value = { type: 'success', message: 'Guardado correctamente.' };
        cancelEdit();
    } catch (error) {
        if (error.response?.status === 422 && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            banner.value = { type: 'error', message: 'Ocurrió un error al guardar.' };
        }
    } finally {
        saving.value = false;
    }
}

async function toggleStatus(item) {
    try {
        const updated = await service.toggle(item.id);
        const idx = items.value.findIndex((i) => i.id === updated.id);
        if (idx !== -1) items.value.splice(idx, 1, updated);
    } catch {
        banner.value = { type: 'error', message: 'No se pudo cambiar el estado.' };
    }
}

function askDelete(item) {
    confirmingDelete.value = item;
}
function cancelDelete() {
    confirmingDelete.value = null;
}
async function confirmDelete() {
    if (!confirmingDelete.value) return;
    const id = confirmingDelete.value.id;
    try {
        await service.remove(id);
        items.value = items.value.filter((i) => i.id !== id);
    } catch (error) {
        banner.value = { type: 'error', message: error.response?.data?.message || 'No se pudo eliminar.' };
    } finally {
        confirmingDelete.value = null;
    }
}

watch(filters, fetchItems, { deep: true });

onMounted(async () => {
    await fetchOptions();
    await fetchItems();
});
</script>

<template>
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Configuración</p>
            <h1 class="font-display text-2xl font-semibold text-text">Maquinaria</h1>
        </div>

        <div
            v-if="banner"
            class="mb-4 rounded-md px-4 py-2.5 text-sm"
            :class="banner.type === 'error' ? 'bg-critical-soft text-critical' : 'bg-good-soft text-good'"
        >
            {{ banner.message }}
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">
            <!-- Formulario: primero en mobile, columna derecha en desktop -->
            <div class="order-first rounded-xl border border-border bg-surface p-5 lg:order-last">
                <h3 class="font-display text-base font-semibold text-text">{{ editing ? 'Editar máquina' : 'Nueva máquina' }}</h3>
                <form class="mt-4 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Nombre</label>
                        <input
                            v-model="form.description"
                            type="text"
                            maxlength="25"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                        <p v-if="errors.description" class="mt-1 text-xs text-critical">{{ errors.description[0] }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Tipo</label>
                        <select
                            v-model="form.machinery_type_id"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                            <option value="" disabled>Selecciona un tipo</option>
                            <option v-for="t in machineryTypes" :key="t.id" :value="t.id">{{ t.description }}</option>
                        </select>
                        <p v-if="errors.machinery_type_id" class="mt-1 text-xs text-critical">{{ errors.machinery_type_id[0] }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Yarda</label>
                        <select
                            v-model="form.field_id"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                            <option value="" disabled>Selecciona una yarda</option>
                            <option v-for="f in fields" :key="f.id" :value="f.id">{{ f.description }}</option>
                        </select>
                        <p v-if="errors.field_id" class="mt-1 text-xs text-critical">{{ errors.field_id[0] }}</p>
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
                            v-if="editing"
                            type="button"
                            class="rounded-md border border-border px-4 py-2.5 text-sm font-semibold text-text hover:bg-surface-2"
                            @click="cancelEdit"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filtros + Tabla: segundo en mobile, columna izquierda en desktop -->
            <div class="order-last lg:order-first">
                <div class="mb-4 flex flex-wrap gap-2">
                    <select v-model="filters.field_id" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text">
                        <option value="">Todas las yardas</option>
                        <option v-for="f in fields" :key="f.id" :value="f.id">{{ f.description }}</option>
                    </select>
                    <select v-model="filters.machinery_type_id" class="rounded-md border border-border bg-surface px-3 py-2 text-sm text-text">
                        <option value="">Todos los tipos</option>
                        <option v-for="t in machineryTypes" :key="t.id" :value="t.id">{{ t.description }}</option>
                    </select>
                </div>

                <CatalogTable :items="items" :loading="loading" empty-message="No hay máquinas registradas." @edit="startEdit" @toggle="toggleStatus" @delete="askDelete">
                    <template #head>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Yarda</th>
                        <th class="px-4 py-3">Estado</th>
                    </template>
                    <template #row="{ item }">
                        <td class="px-4 py-3 font-medium text-text">{{ item.description }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ item.machinery_type.description }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ item.field.description }}</td>
                        <td class="px-4 py-3"><StatusBadge :active="item.active" /></td>
                    </template>
                </CatalogTable>
            </div>
        </div>

        <ConfirmDialog
            :open="!!confirmingDelete"
            title="Eliminar máquina"
            :message="`¿Eliminar “${confirmingDelete?.description}”? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </main>
</template>
