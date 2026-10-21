<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    colors: { type: Array, default: () => ['#9333ea', '#171717'] },
});

const canvas = ref(null);
let chart = null;

function render() {
    if (!canvas.value) return;
    if (chart) chart.destroy();
    chart = new Chart(canvas.value, {
        type: 'doughnut',
        data: {
            labels: props.labels,
            datasets: [{
                data: props.values,
                backgroundColor: props.colors,
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const val = ctx.parsed || 0;
                            return ` ${ctx.label}: $${val.toLocaleString('es-MX')}`;
                        },
                    },
                },
            },
        },
    });
}

onMounted(render);
watch(() => [props.values, props.labels], render, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <canvas ref="canvas"></canvas>
</template>