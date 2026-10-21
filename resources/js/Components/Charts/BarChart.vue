<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import {
    Chart,
    BarElement,
    BarController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(BarElement, BarController, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, default: () => [] },
    ingresos: { type: Array, default: () => [] },
    gastos: { type: Array, default: () => [] },
    labelIngresos: { type: String, default: 'Ingresos' },
    labelGastos: { type: String, default: 'Gastos' },
    colorIngresos: { type: String, default: '#9333ea' }, // purple-600
    colorGastos: { type: String, default: '#171717' },   // neutral-900
});

const canvas = ref(null);
let chart = null;

function compact(n) {
    const abs = Math.abs(n);
    if (abs >= 1_000_000) return '$' + (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (abs >= 1_000) return '$' + (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'k';
    return '$' + n.toLocaleString('es-MX');
}

function full(n) {
    return '$' + Number(n || 0).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function makeGradient(ctx, area, color) {
    if (!area) return color;
    const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    g.addColorStop(0, color);
    g.addColorStop(1, color + 'cc');
    return g;
}

function render() {
    if (!canvas.value) return;
    if (chart) chart.destroy();

    const ctx = canvas.value.getContext('2d');

    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: props.labelIngresos,
                    data: props.ingresos,
                    backgroundColor: (context) => {
                        const { ctx: c, chartArea } = context.chart;
                        return makeGradient(c, chartArea, props.colorIngresos);
                    },
                    hoverBackgroundColor: props.colorIngresos,
                    borderColor: props.colorIngresos,
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 42,
                    categoryPercentage: 0.7,
                    barPercentage: 0.85,
                },
                {
                    label: props.labelGastos,
                    data: props.gastos,
                    backgroundColor: (context) => {
                        const { ctx: c, chartArea } = context.chart;
                        return makeGradient(c, chartArea, props.colorGastos);
                    },
                    hoverBackgroundColor: props.colorGastos,
                    borderColor: props.colorGastos,
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 42,
                    categoryPercentage: 0.7,
                    barPercentage: 0.85,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 900,
                easing: 'easeOutQuart',
            },
            layout: {
                padding: { top: 12, right: 8, bottom: 0, left: 0 },
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(23, 23, 23, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#e5e5e5',
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 12, weight: '600' },
                    padding: 12,
                    cornerRadius: 10,
                    borderColor: 'rgba(255, 255, 255, 0.08)',
                    borderWidth: 1,
                    displayColors: true,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${full(ctx.parsed.y)}`,
                    },
                },
            },
            scales: {
                x: {
                    // ✅ Líneas verticales VISIBLES
                    grid: {
                        display: true,
                        color: 'rgba(23, 23, 23, 0.06)',
                        drawBorder: false,
                        drawTicks: false,
                        lineWidth: 1,
                    },
                    border: { display: false },
                    ticks: {
                        color: '#52525b',
                        font: { size: 11, weight: '600' },
                        maxRotation: 0,
                        autoSkip: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    // ✅ Líneas horizontales VISIBLES
                    grid: {
                        display: true,
                        color: 'rgba(23, 23, 23, 0.06)',
                        drawBorder: false,
                        drawTicks: false,
                        lineWidth: 1,
                    },
                    border: { display: false },
                    ticks: {
                        color: '#71717a',
                        font: { size: 11, weight: '600' },
                        padding: 8,
                        callback: (v) => compact(v),
                        maxTicksLimit: 6,
                    },
                },
            },
        },
    });
}

onMounted(render);
watch(
    () => [props.labels, props.ingresos, props.gastos],
    render,
    { deep: true }
);
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="relative h-full w-full">
        <canvas ref="canvas"></canvas>
    </div>
</template>