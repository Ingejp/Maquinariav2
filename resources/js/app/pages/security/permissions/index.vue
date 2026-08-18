<script setup>
import { onMounted, ref } from 'vue';
import http from '../../../utils/http';
import ConfirmDialog from '../../../components/common/ConfirmDialog.vue';

const permissions = ref([]);
const loading = ref(true);
const newName = ref('');
const creating = ref(false);
const errors = ref({});
const banner = ref(null);
const confirmingDelete = ref(null);
const editing = ref(null);
const editName = ref('');
const savingEdit = ref(false);

async function load() {
    loading.value = true;
    const { data } = await http.get('/seguridad/permisos');
    permissions.value = data.data;
    loading.value = false;
}

async function create() {
    creating.value = true;
    errors.value = {};
    try {
        const { data } = await http.post('/seguridad/permisos', { name: newName.value });
        permissions.value.push(data.data);
        permissions.value.sort((a, b) => a.name.localeCompare(b.name));
        newName.value = '';
    } catch (error) {
        if (error.response?.status === 422 && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            banner.value = { type: 'error', message: 'No se pudo crear el permiso.' };
        }
    } finally {
        creating.value = false;
    }
}

function startEdit(perm) {
    editing.value = perm;
    editName.value = perm.name;
}
function cancelEdit() {
    editing.value = null;
}
async function saveEdit() {
    savingEdit.value = true;
    try {
        const { data } = await http.put(`/seguridad/permisos/${editing.value.id}`, { name: editName.value });
        const idx = permissions.value.findIndex((p) => p.id === data.data.id);
        if (idx !== -1) permissions.value.splice(idx, 1, data.data);
        editing.value = null;
    } catch {
        banner.value = { type: 'error', message: 'No se pudo guardar.' };
    } finally {
        savingEdit.value = false;
    }
}

function askDelete(perm) {
    confirmingDelete.value = perm;
}
function cancelDelete() {
    confirmingDelete.value = null;
}
async function confirmDelete() {
    if (!confirmingDelete.value) return;
    const id = confirmingDelete.value.id;
    try {
        await http.delete(`/seguridad/permisos/${id}`);
        permissions.value = permissions.value.filter((p) => p.id !== id);
    } catch (error) {
        banner.value = { type: 'error', message: error.response?.data?.message || 'No se pudo eliminar.' };
    } finally {
        confirmingDelete.value = null;
    }
}

onMounted(load);
</script>

<template>
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Seguridad</p>
            <h1 class="font-display text-2xl font-semibold text-text">Permisos</h1>
            <p class="mt-1 text-sm text-text-muted">
                catalogs.view, catalogs.manage, report.create, dashboard.view y security.manage están conectados a
                rutas reales — cambiarles el nombre o eliminarlos puede bloquear esas pantallas.
            </p>
        </div>

        <div
            v-if="banner"
            class="mb-4 rounded-md px-4 py-2.5 text-sm"
            :class="banner.type === 'error' ? 'bg-critical-soft text-critical' : 'bg-good-soft text-good'"
        >
            {{ banner.message }}
        </div>

        <form class="mb-1 flex gap-2" @submit.prevent="create">
            <input
                v-model="newName"
                type="text"
                placeholder="nuevo.permiso"
                maxlength="100"
                required
                class="min-w-0 flex-1 rounded-md border border-border px-3 py-2 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
            >
            <button
                type="submit"
                :disabled="creating"
                class="shrink-0 rounded-md bg-accent px-4 py-2 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
            >
                Agregar
            </button>
        </form>
        <p v-if="errors.name" class="mb-4 text-xs text-critical">{{ errors.name[0] }}</p>
        <div v-else class="mb-4"></div>

        <div class="overflow-hidden rounded-xl border border-border bg-surface">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-2 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="3" class="px-4 py-10 text-center text-text-muted">Cargando…</td>
                    </tr>
                    <tr v-else-if="!permissions.length">
                        <td colspan="3" class="px-4 py-10 text-center text-text-muted">Sin permisos.</td>
                    </tr>
                    <tr v-for="perm in permissions" v-else :key="perm.id" class="border-b border-border last:border-0">
                        <td class="px-4 py-3 font-medium text-text">
                            <span v-if="editing?.id !== perm.id">{{ perm.name }}</span>
                            <input
                                v-else
                                v-model="editName"
                                type="text"
                                class="w-full rounded-md border border-border px-2 py-1 text-sm"
                                @keyup.enter="saveEdit"
                            >
                        </td>
                        <td class="px-4 py-3 text-text-muted">{{ perm.roles_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <template v-if="editing?.id === perm.id">
                                    <button type="button" class="text-sm font-medium text-accent hover:text-accent-strong" :disabled="savingEdit" @click="saveEdit">
                                        Guardar
                                    </button>
                                    <button type="button" class="text-sm font-medium text-text-muted hover:text-text" @click="cancelEdit">
                                        Cancelar
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" class="text-sm font-medium text-accent hover:text-accent-strong" @click="startEdit(perm)">
                                        Editar
                                    </button>
                                    <button type="button" class="text-sm font-medium text-critical hover:opacity-80" @click="askDelete(perm)">
                                        Eliminar
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmDialog
            :open="!!confirmingDelete"
            title="Eliminar permiso"
            :message="`¿Eliminar “${confirmingDelete?.name}”? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </main>
</template>
