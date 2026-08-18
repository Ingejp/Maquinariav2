<script setup>
import http from '../../utils/http';

defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    activeModule: {
        type: String,
        default: '',
    },
    username: {
        type: String,
        default: '',
    },
});

async function logout() {
    await http.post('/logout');
    window.location.href = '/login';
}
</script>

<template>
    <nav class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-6">
        <div class="flex flex-wrap items-center gap-4 sm:gap-6">
            <a
                v-for="module in modules"
                :key="module.href"
                :href="module.href"
                class="text-sm font-medium"
                :class="module.key === activeModule ? 'text-slate-900' : 'text-slate-500 hover:text-slate-700'"
            >
                {{ module.label }}
            </a>
        </div>

        <div v-if="username" class="flex items-center gap-3 text-sm text-slate-500">
            <span>{{ username }}</span>
            <button type="button" class="font-medium text-slate-700 hover:text-slate-900" @click="logout">
                Cerrar sesión
            </button>
        </div>
    </nav>
</template>
