<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import SelectInput from '@/Components/SelectInput.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import { formatCurrency } from '@/utils';
import {
    BarChart3, CreditCard, TrendingUp, TrendingDown, Wallet,
    ArrowUpCircle, ArrowDownCircle, CalendarDays, Activity, Landmark,
} from '@lucide/vue';

const props = defineProps({
    anio: Number,
    anios: Array,
    porMes: Array,
    evolucionSaldo: Array,
    resumenDeudas: Object,
    deudas: { type: Array, default: () => [] },
});

function cambiarAnio(e) {
    router.get(route('reportes.index'), { anio: e.target.value }, { preserveState: true });
}

const barData = computed(() => ({
    labels: props.porMes.map((m) => m.nombre),
    ingresos: props.porMes.map((m) => m.ingresos),
    gastos: props.porMes.map((m) => m.gastos),
}));

const lineData = computed(() => ({
    labels: props.evolucionSaldo.map((p) => p.etiqueta),
    values: props.evolucionSaldo.map((p) => p.saldo),
}));

const totalIngresosAnio = computed(() =>
    props.porMes.reduce((s, m) => s + Number(m.ingresos || 0), 0)
);
const totalGastosAnio = computed(() =>
    props.porMes.reduce((s, m) => s + Number(m.gastos || 0), 0)
);
const balanceAnio = computed(() => totalIngresosAnio.value - totalGastosAnio.value);

const progresoDeuda = computed(() => {
    const total = Number(props.resumenDeudas?.total) || 0;
    const pagado = Number(props.resumenDeudas?.pagado) || 0;
    if (total === 0) return 0;
    return Math.min(100, Math.round((pagado / total) * 100));
});

// 👇 Datos para la gráfica de barras de deudas
const deudasBarData = computed(() => {
    const ordenadas = [...props.deudas].sort(
        (a, b) => Number(b.restante || 0) - Number(a.restante || 0)
    );

    return {
        labels: ordenadas.map((d) => d.nombre),
        // Dataset 0 → barra teal (Pagaste)
        pagado: ordenadas.map((d) => Number(d.pagado || 0)),
        // Dataset 1 → barra amber (Debes)
        restante: ordenadas.map((d) => Number(d.restante || 0)),
    };
});
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <BarChart3 :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Reportes
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Cómo se ha movido tu dinero
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden items-center gap-1.5 text-xs font-semibold text-neutral-500 sm:inline-flex">
                        <CalendarDays :size="13" /> Año
                    </span>
                    <SelectInput
                        :model-value="anio"
                        class="w-28 !rounded-xl !border-purple-200 !bg-white !font-semibold !text-purple-800 focus:!border-purple-400 focus:!ring-purple-500"
                        @change="cambiarAnio"
                    >
                        <option v-for="a in anios" :key="a" :value="a">{{ a }}</option>
                    </SelectInput>
                </div>
            </div>

            <!-- Chips resumen del año -->
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-800">
                    Año {{ anio }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-white px-3 py-1 text-xs font-semibold text-purple-800">
                    <ArrowUpCircle :size="11" /> Ingresos: {{ formatCurrency(totalIngresosAnio) }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full border border-neutral-900 bg-neutral-950 px-3 py-1 text-xs font-semibold text-white">
                    <ArrowDownCircle :size="11" /> Gastos: {{ formatCurrency(totalGastosAnio) }}
                </span>
                <span
                    class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="balanceAnio >= 0
                        ? 'border-purple-200 bg-white text-purple-800'
                        : 'border-neutral-900 bg-neutral-950 text-white'"
                >
                    <Activity :size="11" /> Balance: {{ formatCurrency(balanceAnio) }}
                </span>
            </div>

            <!-- Ingresos vs Gastos -->
            <Card class="animate-fade-in overflow-hidden shadow-sm" :padded="false">
                <div class="flex items-center gap-2 border-b border-neutral-100 bg-white px-6 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                        <BarChart3 :size="16" />
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900">Ingresos vs. gastos por mes</h3>
                        <p class="text-xs text-neutral-500">Comparativa mensual del {{ anio }}</p>
                    </div>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="h-[480px] lg:h-[560px]">
                        <BarChart
                            :labels="barData.labels"
                            :ingresos="barData.ingresos"
                            :gastos="barData.gastos"
                            label-ingresos="Ingresos"
                            label-gastos="Gastos"
                            color-ingresos="#9333ea"
                            color-gastos="#171717"
                            class="h-full w-full"
                        />
                    </div>
                </div>
            </Card>

            <!-- Evolución del saldo -->
            <Card class="animate-fade-in mt-6 overflow-hidden shadow-sm" :padded="false">
                <div class="flex items-center gap-2 border-b border-neutral-100 bg-white px-6 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                        <Activity :size="16" />
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900">Evolución del saldo total</h3>
                        <p class="text-xs text-neutral-500">Últimos 6 meses</p>
                    </div>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="h-[300px] lg:h-[360px]">
                        <LineChart
                            :labels="lineData.labels"
                            :values="lineData.values"
                            class="h-full w-full"
                        />
                    </div>
                </div>
            </Card>

            <!-- Deudas: gráfica de barras + stats -->
            <Card class="animate-fade-in mt-6 overflow-hidden shadow-sm" :padded="false">
                <div class="flex items-center justify-between border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                            <CreditCard :size="16" />
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900">Deudas: pagado vs. restante</h3>
                            <p class="text-xs text-neutral-500">
                                {{ resumenDeudas.activas }} activa(s) de {{ resumenDeudas.totalDeudas }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="resumenDeudas.totalDeudas === 0" class="flex flex-col items-center gap-2 px-5 py-16 text-center text-sm text-neutral-400">
                    <CreditCard :size="32" class="text-neutral-200" />
                    No tienes deudas registradas.
                </div>

                <div v-else class="p-6 lg:p-8">

                    <!-- Gráfica de barras de deudas -->
                    <div class="h-[420px] lg:h-[480px]">
                        <BarChart
                            :labels="deudasBarData.labels"
                            :ingresos="deudasBarData.pagado"
                            :gastos="deudasBarData.restante"
                            label-ingresos="Pagaste"
                            label-gastos="Debes"
                            color-ingresos="#9333ea"
                            color-gastos="#171717"
                            :invertir-orden="true"
                            class="h-full w-full"
                        />
                    </div>

                    <!-- Leyenda personalizada (colores actualizados a la nueva paleta) -->
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-6 text-xs font-semibold">
                        <span class="inline-flex items-center gap-1.5 text-teal-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-teal-600"></span> Pagaste
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-amber-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Debes
                        </span>
                    </div>

                    <!-- Progress global -->
                    <div class="mt-6 rounded-xl border border-purple-100 bg-white p-4">
                        <div class="flex items-center justify-between text-xs font-semibold text-neutral-500">
                            <span>Progreso global de pago</span>
                            <span class="text-sm font-bold text-purple-800">{{ progresoDeuda }}%</span>
                        </div>
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-neutral-100">
                            <div
                                class="h-full rounded-full bg-purple-600 transition-all duration-700"
                                :style="{ width: progresoDeuda + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- Grid de stats -->
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="rounded-xl border border-neutral-200 bg-white p-4">
                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                <Wallet :size="13" /> Total
                            </div>
                            <div class="mt-2 text-xl font-bold tabular-nums text-neutral-900">
                                {{ formatCurrency(resumenDeudas.total) }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-purple-200 bg-white p-4">
                            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-neutral-500">
                                <TrendingDown :size="13" /> Pagado
                            </div>
                            <div class="mt-2 text-xl font-bold tabular-nums text-purple-800">
                                {{ formatCurrency(resumenDeudas.pagado) }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4 text-white">
                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-purple-300">
                                <TrendingUp :size="13" /> Restante
                            </div>
                            <div class="mt-2 text-xl font-bold tabular-nums text-white">
                                {{ formatCurrency(resumenDeudas.restante) }}
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-white p-4">
                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                                <Landmark :size="13" /> Activas
                            </div>
                            <div class="mt-2 text-xl font-bold tabular-nums text-neutral-900">
                                {{ resumenDeudas.activas }}
                                <span class="text-sm font-semibold text-neutral-400">/ {{ resumenDeudas.totalDeudas }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>