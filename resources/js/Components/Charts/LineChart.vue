<script setup>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler } from 'chart.js';
import { computed } from 'vue';
import { formatCurrency } from '@/utils';

ChartJS.register(LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler);

const props = defineProps({
    labels: { type: Array, required: true },
    values: { type: Array, required: true },
});

const data = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.values,
            borderColor: '#9333ea',
            backgroundColor: 'rgba(147, 51, 234, 0.08)',
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#9333ea',
            pointRadius: 4,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => ` Saldo: ${formatCurrency(ctx.raw)}` } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
    },
};
</script>

<template>
    <div class="h-64">
        <Line :data="data" :options="options" />
    </div>
</template>
