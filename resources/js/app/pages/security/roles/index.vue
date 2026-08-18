<script setup>
import { onMounted, ref } from 'vue';
import http from '../../../utils/http';
import ConfirmDialog from '../../../components/common/ConfirmDialog.vue';

const roles = ref([]);
const loading = ref(true);
const selected = ref(null);
const allPermissions = ref([]);
const savingPermissions = ref(false);
const newRoleName = ref('');
const creating = ref(false);
const errors = ref({});
const banner = ref(null);
const confirmingDelete = ref(null);

async function loadRoles() {
    loading.value = true;
    const { data } = await http.get('/seguridad/roles');
    roles.value = data.data;
    if (selected.value) {
        selected.value = roles.value.find((r) => r.id === selected.value.id) ?? null;
    }
    loading.value = false;
}

async function loadPermissions() {
    const { data } = await http.get('/seguridad/permisos');
    allPermissions.value = data.data;
}

function select(role) {
    selected.value = role;
}

async function createRole() {
    creating.value = true;
    errors.value = {};
    try {
        const { data } = await http.post('/seguridad/roles', { name: newRoleName.value });
        roles.value.push(data.data);
        roles.value.sort((a, b) => a.name.localeCompare(b.name));
        newRoleName.value = '';
        banner.value = { type: 'success', message: 'Rol creado.' };
    } catch (error) {
        if (error.response?.status === 422 && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            banner.value = { type: 'error', message: 'No se pudo crear el rol.' };
        }
    } finally {
        creating.value = false;
    }
}

async function togglePermission(permissionName) {
    if (!selected.value) return;
    const current = selected.value.permissions;
    const next = current.includes(permissionName)
        ? current.filter((p) => p !== permissionName)
        : [...current, permissionName];

    savingPermissions.value = true;
    try {
        const { data } = await http.patch(`/seguridad/roles/${selected.value.id}/permisos`, { permissions: next });
        selected.value = data.data;
        const idx = roles.value.findIndex((r) => r.id === data.data.id);
        if (idx !== -1) roles.value.splice(idx, 1, data.data);
    } catch {
        banner.value = { type: 'error', message: 'No se pudo actualizar el permiso.' };
    } finally {
        savingPermissions.value = false;
    }
}

function askDelete(role) {
    confirmingDelete.value = role;
}
function cancelDelete() {
    confirmingDelete.value = null;
}
async function confirmDelete() {
    if (!confirmingDelete.value) return;
    const id = confirmingDelete.value.id;
    try {
        await http.delete(`/seguridad/roles/${id}`);
        roles.value = roles.value.filter((r) => r.id !== id);
        if (selected.value?.id === id) selected.value = null;
    } catch (error) {
        banner.value = { type: 'error', message: error.response?.data?.message || 'No se pudo eliminar.' };
    } finally {
        confirmingDelete.value = null;
    }
}

onMounted(async () => {
    await Promise.all([loadRoles(), loadPermissions()]);
});
</script>

<template>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Seguridad</p>
            <h1 class="font-display text-2xl font-semibold text-text">Roles</h1>
        </div>

        <div
            v-if="banner"
            class="mb-4 rounded-md px-4 py-2.5 text-sm"
            :class="banner.type === 'error' ? 'bg-critical-soft text-critical' : 'bg-good-soft text-good'"
        >
            {{ banner.message }}
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[280px_1fr]">
            <div>
                <div class="mb-4 rounded-xl border border-border bg-surface p-4">
                    <form class="flex gap-2" @submit.prevent="createRole">
                        <input
                            v-model="newRoleName"
                            type="text"
                            placeholder="Nuevo rol"
                            maxlength="50"
                            required
                            class="min-w-0 flex-1 rounded-md border border-border px-3 py-2 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                        <button
                            type="submit"
                            :disabled="creating"
                            class="shrink-0 rounded-md bg-accent px-3 py-2 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
                        >
                            +
                        </button>
                    </form>
                    <p v-if="errors.name" class="mt-1 text-xs text-critical">{{ errors.name[0] }}</p>
                </div>

                <div class="overflow-hidden rounded-xl border border-border bg-surface">
                    <button
                        v-for="role in roles"
                        :key="role.id"
                        type="button"
                        class="flex w-full items-center justify-between border-b border-border px-4 py-3 text-left last:border-0"
                        :class="selected?.id === role.id ? 'bg-accent-soft' : 'hover:bg-surface-2'"
                        @click="select(role)"
                    >
                        <span class="text-sm font-medium text-text">{{ role.name }}</span>
                        <span class="text-xs text-text-muted">{{ role.users_count }}</span>
                    </button>
                    <p v-if="!loading && !roles.length" class="px-4 py-6 text-center text-sm text-text-muted">Sin roles.</p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-surface p-5">
                <div v-if="!selected" class="py-16 text-center text-sm text-text-muted">Selecciona un rol para ver y editar sus permisos.</div>
                <div v-else>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-display text-lg font-semibold text-text">{{ selected.name }}</h2>
                        <button type="button" class="text-sm font-medium text-critical hover:opacity-80" @click="askDelete(selected)">
                            Eliminar rol
                        </button>
                    </div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-text-muted">Permisos</p>
                    <div class="space-y-2">
                        <label v-for="perm in allPermissions" :key="perm.id" class="flex items-center gap-3 rounded-md border border-border px-3 py-2.5">
                            <input
                                type="checkbox"
                                class="h-4 w-4 accent-accent"
                                :checked="selected.permissions.includes(perm.name)"
                                :disabled="savingPermissions"
                                @change="togglePermission(perm.name)"
                            >
                            <span class="text-sm text-text">{{ perm.name }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="!!confirmingDelete"
            title="Eliminar rol"
            :message="`¿Eliminar el rol “${confirmingDelete?.name}”? Esta acción no se puede deshacer.`"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </main>
</template>
