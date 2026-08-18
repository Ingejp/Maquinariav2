<script setup>
import { useRouter } from 'vue-router';

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const router = useRouter();
</script>

<template>
    <div class="sticky top-0 z-10 flex items-center gap-3 border-b border-border bg-surface px-4 py-3">
        <button
            type="button"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-lg text-text-muted hover:bg-surface-2"
            aria-label="Volver"
            @click="router.back()"
        >
            ←
        </button>
        <div class="min-w-0">
            <p v-if="subtitle" class="truncate text-xs text-text-muted">{{ subtitle }}</p>
            <h1 class="truncate font-display text-lg font-semibold text-text">{{ title }}</h1>
        </div>
    </div>
</template>
