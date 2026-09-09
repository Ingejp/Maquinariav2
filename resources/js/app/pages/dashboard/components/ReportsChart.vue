<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    chartData: {
        type: Object,
        required: true,
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const palette = {
    good: '#2f8f52',
    warn: '#b9791f',
    critical: '#c0392b',
    neutral: '#9aa2ad',
};

function buildConfig() {
    return {
        type: 'bar',
        data: {
            labels: props.chartData.labels,
            datasets: props.chartData.datasets.map((d) => ({
                label: d.label,
                data: d.data,
                backgroundColor: palette[d.class] || palette.neutral,
                borderRadius: 4,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    };
}

function render() {
    if (chartInstance) {
        chartInstance.destroy();
    }
    chartInstance = new Chart(canvasRef.value, buildConfig());
}

onMounted(render);
watch(() => props.chartData, render, { deep: true });
onBeforeUnmount(() => chartInstance?.destroy());
</script>

<template>
    <div class="h-72">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>
