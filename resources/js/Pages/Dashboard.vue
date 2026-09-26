<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { exito, error as mostrarError } from '@/lib/alertas';
import { Wallet, ArrowUpCircle, ArrowDownCircle, TrendingDown, Briefcase, Repeat, CheckCircle2, Landmark, Tag, ChartColumn } from '@lucide/vue';

const props = defineProps({
    resumen: Object,
    cuentas: Array,
    gastosPorCategoria: Array,
    ultimosMovimientos: Array,
    proximosCargos: Array,
    mesActual: String,
});

function pagarCargo(cargo) {
    router.post(
        route('recurrentes.pagar', cargo.id),
        { fecha: hoyIso() },
        {
            preserveScroll: true,
            onSuccess: () => exito(`Pago de "${cargo.nombre}" registrado.`),
            onError: (errors) => mostrarError(Object.values(errors)[0] || 'No se pudo registrar el pago.'),
        },
    );
}

const maxCategoria = computed(() => Math.max(1, ...props.gastosPorCategoria.map((c) => Number(c.total))));
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout title="Inicio" subtitle="Resumen general de tus finanzas">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="animate-fade-in" style="animation-delay: 0ms">
                <StatCard label="Saldo total" :value="formatCurrency(resumen.saldoTotal)" :icon="Wallet" color="#9333ea" />
            </div>
            <div class="animate-fade-in" style="animation-delay: 60ms">
                <StatCard label="Ingresos del mes" :value="formatCurrency(resumen.ingresosMes)" :icon="ArrowUpCircle" color="#16a34a" />
            </div>
            <div class="animate-fade-in" style="animation-delay: 120ms">
                <StatCard label="Gastos del mes" :value="formatCurrency(resumen.gastosMes)" :icon="ArrowDownCircle" color="#e11d48" />
            </div>
            <div class="animate-fade-in" style="animation-delay: 180ms">
                <StatCard
                    label="Deuda pendiente"
                    :value="formatCurrency(resumen.deudaPendiente)"
                    :icon="TrendingDown"
                    color="#dc2626"
                    :subtitle="`${resumen.numDeudasActivas} deuda(s) activa(s)`"
                />
            </div>
            <div class="animate-fade-in" style="animation-delay: 240ms">
                <StatCard
                    label="Por cobrar (proyectos)"
                    :value="formatCurrency(resumen.proyectosPorCobrar)"
                    :icon="Briefcase"
                    color="#0ea5e9"
                    :subtitle="`${resumen.numProyectosActivos} proyecto(s) activo(s)`"
                />
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <Card title="Mis cuentas" :icon="Landmark" class="animate-fade-in">
                <template #actions>
                    <Link :href="route('cuentas.index')" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:underline">
                        <Landmark :size="13" /> Ver todas
                    </Link>
                </template>
                <div v-if="cuentas.length === 0" class="py-6 text-center text-sm text-ink-400">
                    Aún no tienes cuentas.
                    <Link :href="route('cuentas.index')" class="font-semibold text-brand-700 hover:underline">Crea la primera</Link>
                </div>
                <ul v-else class="divide-y divide-ink-100">
                    <li v-for="c in cuentas" :key="c.id" class="flex items-center justify-between py-3 transition-colors hover:bg-ink-50/60">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg" :style="{ backgroundColor: c.color + '18', color: c.color }">
                                <Wallet :size="13" />
                            </span>
                            <span class="text-sm font-semibold text-ink-800">{{ c.nombre }}</span>
                        </div>
                        <span class="text-sm font-bold" :class="c.saldo < 0 ? 'text-rose-600' : 'text-ink-900'">
                            {{ formatCurrency(c.saldo) }}
                        </span>
                    </li>
                </ul>
            </Card>

            <Card :title="`Gastos de ${mesActual} por categoría`" :icon="ChartColumn" class="animate-fade-in" style="animation-delay: 80ms">
                <div v-if="gastosPorCategoria.length === 0" class="flex h-56 items-center justify-center text-sm text-ink-400">
                    Sin gastos este mes.
                </div>
                <ul v-else class="space-y-3">
                    <li v-for="c in gastosPorCategoria" :key="c.categoria">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-ink-800">
                                <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: c.color }" />
                                {{ c.categoria }}
                            </span>
                            <span class="font-bold text-ink-900">{{ formatCurrency(c.total) }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-ink-100">
                            <div
                                class="h-full rounded-full transition-all duration-500 ease-out"
                                :style="{ width: (Number(c.total) / maxCategoria) * 100 + '%', backgroundColor: c.color }"
                            />
                        </div>
                    </li>
                </ul>
            </Card>
        </div>

        <Card v-if="proximosCargos.length > 0" title="Próximos cobros recurrentes" :icon="Repeat" class="mt-6 animate-fade-in">
            <template #actions>
                <Link :href="route('recurrentes.index')" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:underline">
                    <Repeat :size="13" /> Ver todos
                </Link>
            </template>
            <ul class="divide-y divide-ink-100">
                <li v-for="c in proximosCargos" :key="c.id" class="flex items-center justify-between gap-3 py-3 transition-colors hover:bg-ink-50/60">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                            <Repeat :size="15" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-ink-800">{{ c.nombre }}</div>
                            <div class="text-xs text-ink-400">{{ formatFecha(c.proxima_fecha) }} · {{ c.cuenta.nombre }}</div>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="text-sm font-bold text-ink-900">{{ formatCurrency(c.monto) }}</span>
                        <button
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-700"
                            @click="pagarCargo(c)"
                        >
                            <CheckCircle2 :size="12" /> Pagar
                        </button>
                    </div>
                </li>
            </ul>
        </Card>

        <Card title="Últimos movimientos" :icon="Wallet" class="mt-6 animate-fade-in" :padded="false" style="animation-delay: 100ms">
            <template #actions>
                <Link :href="route('movimientos.index')" class="inline-flex items-center gap-1 px-5 text-xs font-semibold text-brand-700 hover:underline">Ver todos</Link>
            </template>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-left text-xs font-semibold uppercase tracking-wide text-ink-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Cuenta</th>
                            <th class="px-5 py-3">Categoría</th>
                            <th class="px-5 py-3">Descripción</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="ultimosMovimientos.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-ink-400">
                                <div class="flex flex-col items-center gap-2">
                                    <Wallet :size="26" class="text-ink-200" />
                                    Aún no tienes movimientos registrados.
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="(m, i) in ultimosMovimientos"
                            :key="m.id"
                            class="animate-fade-in border-b border-ink-50 transition-colors last:border-0 odd:bg-ink-50/30 hover:!bg-brand-50/40"
                            :style="{ animationDelay: `${i * 25}ms` }"
                        >
                            <td class="px-5 py-3 text-ink-500">{{ formatFecha(m.fecha) }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ m.cuenta.nombre }}</td>
                            <td class="px-5 py-3">
                                <Badge v-if="m.categoria" :color="m.categoria.color"><Tag :size="10" class="mr-1" />{{ m.categoria.nombre }}</Badge>
                                <span v-else class="text-ink-300">-</span>
                            </td>
                            <td class="px-5 py-3 text-ink-500">{{ m.descripcion || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold" :class="m.tipo === 'ingreso' ? 'text-brand-700' : 'text-rose-600'">
                                <span class="inline-flex items-center gap-1">
                                    <component :is="m.tipo === 'ingreso' ? ArrowUpCircle : ArrowDownCircle" :size="13" />
                                    {{ formatCurrency(m.monto) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
