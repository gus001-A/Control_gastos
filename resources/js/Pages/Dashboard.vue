<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatCurrency, formatFecha } from '@/utils';
import DonutChart from '@/Components/Charts/DonutChart.vue';

const props = defineProps({
    resumen: Object,
    cuentas: Array,
    gastosPorCategoria: Array,
    ultimosMovimientos: Array,
    mesActual: String,
});

const chartData = computed(() => ({
    labels: props.gastosPorCategoria.map((c) => c.categoria),
    values: props.gastosPorCategoria.map((c) => Number(c.total)),
    colors: props.gastosPorCategoria.map((c) => c.color),
}));
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout title="Inicio" subtitle="Resumen general de tus finanzas">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard label="Saldo total" :value="formatCurrency(resumen.saldoTotal)" icon="💰" color="#16a34a" />
            <StatCard label="Ingresos del mes" :value="formatCurrency(resumen.ingresosMes)" icon="⬆️" color="#16a34a" />
            <StatCard label="Gastos del mes" :value="formatCurrency(resumen.gastosMes)" icon="⬇️" color="#e11d48" />
            <StatCard
                label="Deuda pendiente"
                :value="formatCurrency(resumen.deudaPendiente)"
                icon="📉"
                color="#dc2626"
                :subtitle="`${resumen.numDeudasActivas} deuda(s) activa(s)`"
            />
            <StatCard
                label="Por cobrar (proyectos)"
                :value="formatCurrency(resumen.proyectosPorCobrar)"
                icon="💼"
                color="#0ea5e9"
                :subtitle="`${resumen.numProyectosActivos} proyecto(s) activo(s)`"
            />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <Card title="Mis cuentas">
                <template #actions>
                    <Link :href="route('cuentas.index')" class="text-xs font-semibold text-brand-700 hover:underline">Ver todas</Link>
                </template>
                <div v-if="cuentas.length === 0" class="py-6 text-center text-sm text-gray-400">
                    Aún no tienes cuentas.
                    <Link :href="route('cuentas.index')" class="font-semibold text-brand-700 hover:underline">Crea la primera</Link>
                </div>
                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="c in cuentas" :key="c.id" class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: c.color }" />
                            <span class="text-sm font-semibold text-ink-800">{{ c.nombre }}</span>
                        </div>
                        <span class="text-sm font-bold" :class="c.saldo < 0 ? 'text-rose-600' : 'text-ink-900'">
                            {{ formatCurrency(c.saldo) }}
                        </span>
                    </li>
                </ul>
            </Card>

            <Card :title="`Gastos de ${mesActual} por categoría`">
                <div v-if="gastosPorCategoria.length === 0" class="flex h-56 items-center justify-center text-sm text-gray-400">
                    Sin gastos este mes.
                </div>
                <DonutChart v-else :labels="chartData.labels" :values="chartData.values" :colors="chartData.colors" />
            </Card>
        </div>

        <Card title="Últimos movimientos" class="mt-6" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Cuenta</th>
                            <th class="px-5 py-3">Categoría</th>
                            <th class="px-5 py-3">Descripción</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="ultimosMovimientos.length === 0">
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">Aún no tienes movimientos registrados.</td>
                        </tr>
                        <tr v-for="m in ultimosMovimientos" :key="m.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60">
                            <td class="px-5 py-3 text-gray-500">{{ formatFecha(m.fecha) }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ m.cuenta.nombre }}</td>
                            <td class="px-5 py-3">
                                <Badge v-if="m.categoria" :color="m.categoria.color">{{ m.categoria.nombre }}</Badge>
                                <span v-else class="text-gray-300">-</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ m.descripcion || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold" :class="m.tipo === 'ingreso' ? 'text-brand-700' : 'text-rose-600'">
                                {{ m.tipo === 'ingreso' ? '+' : '-' }} {{ formatCurrency(m.monto) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
