<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import SelectInput from '@/Components/SelectInput.vue';
import DonutChart from '@/Components/Charts/DonutChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import HorizontalBarChart from '@/Components/Charts/HorizontalBarChart.vue';
import { formatCurrency } from '@/utils';

const props = defineProps({
    anio: Number,
    anios: Array,
    gastosPorCategoria: Array,
    porMes: Array,
    evolucionSaldo: Array,
    proyectos: Array,
    resumenDeudas: Object,
});

function cambiarAnio(e) {
    router.get(route('reportes.index'), { anio: e.target.value }, { preserveState: true });
}

const pieData = computed(() => ({
    labels: props.gastosPorCategoria.map((c) => c.categoria),
    values: props.gastosPorCategoria.map((c) => Number(c.total)),
    colors: props.gastosPorCategoria.map((c) => c.color),
}));

const barData = computed(() => ({
    labels: props.porMes.map((m) => m.nombre),
    ingresos: props.porMes.map((m) => m.ingresos),
    gastos: props.porMes.map((m) => m.gastos),
}));

const lineData = computed(() => ({
    labels: props.evolucionSaldo.map((p) => p.etiqueta),
    values: props.evolucionSaldo.map((p) => p.saldo),
}));

const proyectosData = computed(() => ({
    labels: props.proyectos.map((p) => (p.nombre.length > 18 ? p.nombre.slice(0, 18) + '…' : p.nombre)),
    pagado: props.proyectos.map((p) => p.pagado),
    pendiente: props.proyectos.map((p) => p.pendiente),
}));
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout title="Reportes" subtitle="Cómo se ha movido tu dinero">
        <div class="mb-5 flex justify-end">
            <SelectInput :model-value="anio" class="w-32" @change="cambiarAnio">
                <option v-for="a in anios" :key="a" :value="a">{{ a }}</option>
            </SelectInput>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <Card title="Gastos por categoría (año seleccionado)">
                <div v-if="gastosPorCategoria.length === 0" class="flex h-56 items-center justify-center text-sm text-ink-400">
                    Sin gastos registrados.
                </div>
                <DonutChart v-else :labels="pieData.labels" :values="pieData.values" :colors="pieData.colors" />
            </Card>

            <Card title="Ingresos vs. gastos por mes">
                <BarChart :labels="barData.labels" :ingresos="barData.ingresos" :gastos="barData.gastos" />
            </Card>

            <Card title="Evolución del saldo total (últimos 6 meses)">
                <LineChart :labels="lineData.labels" :values="lineData.values" />
            </Card>

            <Card title="Rentabilidad de proyectos freelance">
                <div v-if="proyectos.length === 0" class="flex h-56 items-center justify-center text-sm text-ink-400">
                    Sin proyectos registrados.
                </div>
                <HorizontalBarChart v-else :labels="proyectosData.labels" :pagado="proyectosData.pagado" :pendiente="proyectosData.pendiente" />
            </Card>
        </div>

        <Card title="Resumen de deudas" class="mt-6">
            <div v-if="resumenDeudas.totalDeudas === 0" class="text-sm text-ink-400">No tienes deudas registradas.</div>
            <div v-else class="flex flex-wrap gap-x-8 gap-y-2 text-sm text-ink-600">
                <span>Total histórico: <strong class="text-ink-900">{{ formatCurrency(resumenDeudas.total) }}</strong></span>
                <span>Pagado: <strong class="text-brand-700">{{ formatCurrency(resumenDeudas.pagado) }}</strong></span>
                <span>Restante: <strong class="text-rose-600">{{ formatCurrency(resumenDeudas.restante) }}</strong></span>
                <span>Deudas activas: <strong class="text-ink-900">{{ resumenDeudas.activas }} de {{ resumenDeudas.totalDeudas }}</strong></span>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
