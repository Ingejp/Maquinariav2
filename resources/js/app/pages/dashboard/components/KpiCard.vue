<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    tone: {
        type: String,
        default: 'default',
    },
});

const toneClasses = {
    good: 'text-good',
    warn: 'text-warn',
    critical: 'text-critical',
    neutral: 'text-text-muted',
    default: 'text-text',
};
</script>

<template>
    <div class="rounded-xl border border-border bg-surface p-4">
        <p class="text-xs font-medium text-text-muted">{{ label }}</p>
        <p class="mt-1 font-display text-2xl font-semibold" :class="toneClasses[tone] || toneClasses.default">{{ value }}</p>
    </div>
</template>
