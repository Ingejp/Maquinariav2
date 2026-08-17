<script setup>
defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
    activeModule: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <nav class="flex items-center gap-6 border-b border-slate-200 px-6 py-3">
        <a
            v-for="module in modules"
            :key="module.href"
            :href="module.href"
            class="text-sm font-medium"
            :class="module.key === activeModule ? 'text-slate-900' : 'text-slate-500 hover:text-slate-700'"
        >
            {{ module.label }}
        </a>
    </nav>
</template>
