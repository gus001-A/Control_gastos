<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';
import { computed } from 'vue';
import { formatCurrency } from '@/utils';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, required: true },
    pagado: { type: Array, required: true },
    pendiente: { type: Array, required: true },
});

const data = computed(() => ({
    labels: props.labels,
    datasets: [
        { label: 'Pagado', data: props.pagado, backgroundColor: '#16a34a', borderRadius: 4 },
        { label: 'Pendiente', data: props.pendiente, backgroundColor: '#fbbf24', borderRadius: 4 },
    ],
}));

const options = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 }, color: '#475569' } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}` } },
    },
    scales: {
        x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
    },
};
</script>

<template>
    <div class="h-64">
        <Bar :data="data" :options="options" />
    </div>
</template>
