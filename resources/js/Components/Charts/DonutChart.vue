<script setup>
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { computed } from 'vue';
import { formatCurrency } from '@/utils';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, required: true },
    values: { type: Array, required: true },
    colors: { type: Array, required: true },
});

const data = computed(() => ({
    labels: props.labels,
    datasets: [{ data: props.values, backgroundColor: props.colors, borderWidth: 0, hoverOffset: 6 }],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '62%',
    plugins: {
        legend: {
            position: 'right',
            labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 }, color: '#475569' },
        },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.label}: ${formatCurrency(ctx.raw)}`,
            },
        },
    },
};
</script>

<template>
    <div class="h-56">
        <Doughnut :data="data" :options="options" />
    </div>
</template>
