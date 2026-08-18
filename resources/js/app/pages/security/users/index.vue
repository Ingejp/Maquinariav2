<script setup>
import { onMounted, ref } from 'vue';
import http from '../../../utils/http';

const users = ref([]);
const roles = ref([]);
const loading = ref(true);
const banner = ref(null);

const emptyForm = () => ({ username: '', password: '', password_confirmation: '', role: '' });
const form = ref(emptyForm());
const creating = ref(false);
const errors = ref({});

async function loadUsers() {
    loading.value = true;
    const { data } = await http.get('/seguridad/usuarios');
    users.value = data.data;
    loading.value = false;
}

async function loadRoles() {
    const { data } = await http.get('/seguridad/roles');
    roles.value = data.data;
}

async function createUser() {
    creating.value = true;
    errors.value = {};
    try {
        const { data } = await http.post('/seguridad/usuarios', form.value);
        users.value.push(data.data);
        users.value.sort((a, b) => a.username.localeCompare(b.username));
        form.value = emptyForm();
        banner.value = { type: 'success', message: 'Usuario creado.' };
    } catch (error) {
        if (error.response?.status === 422 && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            banner.value = { type: 'error', message: 'No se pudo crear el usuario.' };
        }
    } finally {
        creating.value = false;
    }
}

async function changeRole(user, event) {
    const role = event.target.value;
    try {
        const { data } = await http.patch(`/seguridad/usuarios/${user.id}/rol`, { role });
        const idx = users.value.findIndex((u) => u.id === user.id);
        if (idx !== -1) users.value.splice(idx, 1, data.data);
    } catch {
        banner.value = { type: 'error', message: 'No se pudo cambiar el rol.' };
    }
}

onMounted(async () => {
    await Promise.all([loadUsers(), loadRoles()]);
});
</script>

<template>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">Seguridad</p>
            <h1 class="font-display text-2xl font-semibold text-text">Usuarios</h1>
        </div>

        <div
            v-if="banner"
            class="mb-4 rounded-md px-4 py-2.5 text-sm"
            :class="banner.type === 'error' ? 'bg-critical-soft text-critical' : 'bg-good-soft text-good'"
        >
            {{ banner.message }}
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
            <div class="overflow-hidden rounded-xl border border-border bg-surface">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-surface-2 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="2" class="px-4 py-10 text-center text-text-muted">Cargando…</td>
                        </tr>
                        <tr v-for="u in users" v-else :key="u.id" class="border-b border-border last:border-0">
                            <td class="px-4 py-3 font-medium text-text">{{ u.username }}</td>
                            <td class="px-4 py-3">
                                <select
                                    :value="u.role"
                                    class="rounded-md border border-border bg-surface px-2 py-1.5 text-sm text-text"
                                    @change="changeRole(u, $event)"
                                >
                                    <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rounded-xl border border-border bg-surface p-5">
                <h3 class="font-display text-base font-semibold text-text">Nuevo usuario</h3>
                <p class="mb-4 text-xs text-text-muted">No hay registro público — solo se crean cuentas desde acá.</p>
                <form class="space-y-4" @submit.prevent="createUser">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Usuario</label>
                        <input
                            v-model="form.username"
                            type="text"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                        <p v-if="errors.username" class="mt-1 text-xs text-critical">{{ errors.username[0] }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Contraseña</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            minlength="10"
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                        <p v-if="errors.password" class="mt-1 text-xs text-critical">{{ errors.password[0] }}</p>
                        <p v-else class="mt-1 text-xs text-text-muted">Mínimo 10 caracteres.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Confirmar contraseña</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-text-muted">Rol</label>
                        <select
                            v-model="form.role"
                            required
                            class="w-full rounded-md border border-border px-3 py-2.5 text-sm text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                            <option value="" disabled>Selecciona un rol</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                        </select>
                        <p v-if="errors.role" class="mt-1 text-xs text-critical">{{ errors.role[0] }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="creating"
                        class="w-full rounded-md bg-accent px-4 py-2.5 text-sm font-semibold text-on-accent hover:bg-accent-strong disabled:opacity-60"
                    >
                        {{ creating ? 'Creando…' : 'Crear usuario' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>
