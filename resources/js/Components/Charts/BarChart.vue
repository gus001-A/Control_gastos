<script setup>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';
import { computed } from 'vue';
import { formatCurrency } from '@/utils';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, required: true },
    ingresos: { type: Array, required: true },
    gastos: { type: Array, required: true },
});

const data = computed(() => ({
    labels: props.labels,
    datasets: [
        { label: 'Ingresos', data: props.ingresos, backgroundColor: '#16a34a', borderRadius: 4, maxBarThickness: 22 },
        { label: 'Gastos', data: props.gastos, backgroundColor: '#e11d48', borderRadius: 4, maxBarThickness: 22 },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 }, color: '#475569' } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}` } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
    },
};
</script>

<template>
    <div class="h-64">
        <Bar :data="data" :options="options" />
    </div>
</template>
