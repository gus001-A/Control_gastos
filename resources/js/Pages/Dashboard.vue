<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import Badge from '@/Components/Badge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { exito, error as mostrarError } from '@/lib/alertas';
import {
    Wallet, ArrowUpCircle, ArrowDownCircle, TrendingDown, Briefcase,
    Repeat, CheckCircle2, Landmark, Tag, ChartColumn, TrendingUp,
    PiggyBank, Activity, CalendarDays,
} from '@lucide/vue';

const props = defineProps({
    resumen: { type: Object, default: () => ({}) },
    cuentas: { type: Array, default: () => [] },
    gastosPorCategoria: { type: Array, default: () => [] },
    ultimosMovimientos: { type: Array, default: () => [] },
    proximosCargos: { type: Array, default: () => [] },
    mesActual: { type: String, default: 'este mes' },
});

const resumen = computed(() => ({
    saldoTotal: 0,
    ingresosMes: 0,
    gastosMes: 0,
    deudaPendiente: 0,
    numDeudasActivas: 0,
    proyectosPorCobrar: 0,
    numProyectosActivos: 0,
    ...(props.resumen || {}),
}));

function pagarCargo(cargo) {
    router.post(
        route('recurrentes.pagar', cargo.id),
        { fecha: hoyIso() },
        {
            preserveScroll: true,
            onSuccess: () => exito(`Pago de "${cargo.nombre}" registrado.`),
            onError: (errors) =>
                mostrarError(Object.values(errors)[0] || 'No se pudo registrar el pago.'),
        },
    );
}

const maxCategoria = computed(() =>
    Math.max(1, ...props.gastosPorCategoria.map((c) => Number(c.total) || 0))
);

const balanceMes = computed(
    () => Number(resumen.value.ingresosMes) - Number(resumen.value.gastosMes)
);

const porcentajeGasto = computed(() => {
    const ing = Number(resumen.value.ingresosMes) || 0;
    const gas = Number(resumen.value.gastosMes) || 0;
    if (ing === 0) return 0;
    return Math.min(100, Math.round((gas / ing) * 100));
});

const ratioGasto = computed(() => {
    const ing = Number(resumen.value.ingresosMes) || 0;
    const gas = Number(resumen.value.gastosMes) || 0;
    const max = Math.max(ing, gas, 1);
    return {
        ingreso: (ing / max) * 100,
        gasto: (gas / max) * 100,
    };
});

const colorCuenta = (c) => c?.color || '#7c3aed';

// --- helper de color (mismo que en la Vue de Movimientos) ---
function hexToRgba(hex, alpha = 1) {
    if (!hex) return `rgba(148, 163, 184, ${alpha})`;
    const h = hex.replace('#', '');
    const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
    const num = parseInt(full, 16);
    const r = (num >> 16) & 255;
    const g = (num >> 8) & 255;
    const b = num & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px] px-1 sm:px-2 lg:px-4">

            <!-- Banner principal -->
            <div
                class="animate-fade-in relative mb-6 overflow-hidden rounded-3xl bg-neutral-950 p-6 text-white shadow-2xl shadow-purple-950/40 lg:p-8"
            >
                <!-- Fondo animado: manchas moradas flotando -->
                <div class="pointer-events-none absolute -left-20 -top-24 h-72 w-72 animate-float rounded-full bg-purple-600/40 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 right-10 h-80 w-80 animate-float-slow rounded-full bg-purple-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute right-1/3 top-0 h-40 w-40 animate-float rounded-full bg-fuchsia-500/20 blur-3xl" style="animation-delay: -6s"></div>

                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="mb-1 inline-flex items-center gap-1.5 rounded-full border border-purple-400/30 bg-purple-500/20 px-3 py-1 text-[11px] font-semibold text-purple-100 backdrop-blur">
                            <CalendarDays :size="12" /> Resumen de {{ mesActual }}
                        </div>
                        <h2 class="mt-2 text-sm font-medium text-neutral-400">
                            Balance del mes
                        </h2>
                        <p
                            class="mt-1 animate-scale-in text-4xl font-bold tracking-tight tabular-nums lg:text-5xl"
                            :class="balanceMes >= 0 ? 'bg-gradient-to-r from-white via-purple-200 to-purple-400 bg-clip-text text-transparent' : 'text-rose-400'"
                        >
                            {{ formatCurrency(balanceMes) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-purple-400/20 bg-white/5 p-4 backdrop-blur-md transition-transform duration-300 hover:scale-105">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-purple-800 text-white shadow-lg shadow-purple-900/50">
                            <Activity :size="22" class="animate-pulse" />
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-wider text-neutral-400">Uso del ingreso</p>
                            <p class="text-xl font-semibold tabular-nums text-white">{{ porcentajeGasto }}%</p>
                        </div>
                    </div>
                </div>

                <div class="relative mt-5 space-y-2 lg:mt-6 lg:max-w-3xl">
                    <div class="flex items-center justify-between text-xs font-medium text-neutral-300">
                        <span class="inline-flex items-center gap-1"><ArrowUpCircle :size="13" /> Ingresos</span>
                        <span>{{ formatCurrency(resumen.ingresosMes) }}</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-white/10">
                        <div
                            class="barra-brillo h-full rounded-full bg-gradient-to-r from-purple-400 to-purple-600 transition-all duration-700"
                            :style="{ width: ratioGasto.ingreso + '%' }"
                        ></div>
                    </div>

                    <div class="flex items-center justify-between text-xs font-medium text-neutral-300">
                        <span class="inline-flex items-center gap-1"><ArrowDownCircle :size="13" /> Gastos</span>
                        <span>{{ formatCurrency(resumen.gastosMes) }}</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-white/10">
                        <div
                            class="h-full rounded-full bg-white transition-all duration-700"
                            :style="{ width: ratioGasto.gasto + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Stat cards (sin "Por cobrar") -->
            <div class="stagger grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
                <div class="h-[132px]" style="animation-delay: 0ms">
                    <StatCard
                        class="h-full"
                        label="Saldo total"
                        :value="formatCurrency(resumen.saldoTotal)"
                        :icon="Wallet"
                        oscuro
                    />
                </div>
                <div class="h-[132px]" style="animation-delay: 60ms">
                    <StatCard
                        class="h-full"
                        label="Ingresos del mes"
                        :value="formatCurrency(resumen.ingresosMes)"
                        :icon="ArrowUpCircle"
                    />
                </div>
                <div class="h-[132px]" style="animation-delay: 120ms">
                    <StatCard
                        class="h-full"
                        label="Gastos del mes"
                        :value="formatCurrency(resumen.gastosMes)"
                        :icon="ArrowDownCircle"
                    />
                </div>
                <div class="h-[132px]" style="animation-delay: 180ms">
                    <StatCard
                        class="h-full"
                        label="Deuda pendiente"
                        :value="formatCurrency(resumen.deudaPendiente)"
                        :icon="TrendingDown"
                        :subtitle="`${resumen.numDeudasActivas} deuda(s) activa(s)`"
                    />
                </div>
            </div>

            <!-- Cuentas + Categorias -->
            <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

                <!-- Cuentas -->
                <Card class="animate-fade-in overflow-hidden shadow-sm">
                    <div class="border-b border-neutral-100 bg-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                    <Landmark :size="16" />
                                </span>
                                <div>
                                    <h3 class="text-sm font-semibold text-neutral-900">Mis cuentas</h3>
                                    <p class="text-xs text-neutral-500">{{ cuentas.length }} cuenta(s)</p>
                                </div>
                            </div>
                            <Link
                                :href="route('cuentas.index')"
                                class="rounded-lg border border-purple-200 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 transition hover:border-purple-400 hover:bg-purple-50"
                            >
                                Ver todas
                            </Link>
                        </div>
                    </div>

                    <div v-if="cuentas.length === 0" class="py-10 text-center text-sm text-neutral-400">
                        Aun no tienes cuentas.
                        <Link :href="route('cuentas.index')" class="font-semibold text-purple-700 hover:underline">
                            Crea la primera
                        </Link>
                    </div>

                    <ul v-else class="divide-y divide-neutral-100">
                        <li
                            v-for="(c, i) in cuentas"
                            :key="c.id"
                            class="animate-fade-in group flex items-center justify-between px-6 py-4 transition-all hover:bg-purple-50/60"
                            :style="{ animationDelay: `${i * 40}ms` }"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="relative flex h-10 w-10 items-center justify-center rounded-xl transition-transform"
                                    :style="{
                                        backgroundColor: hexToRgba(colorCuenta(c), 0.15),
                                        color: colorCuenta(c),
                                        '--tw-ring-color': hexToRgba(colorCuenta(c), 0.35),
                                    }"
                                >
                                    <Wallet :size="16" />
                                    <span
                                        class="absolute inset-0 rounded-xl ring-2 ring-inset"
                                        :style="{ '--tw-ring-color': hexToRgba(colorCuenta(c), 0.35) }"
                                    ></span>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-neutral-900">{{ c.nombre }}</p>
                                    <p class="text-[11px] text-neutral-500">{{ c.tipo || 'Cuenta' }}</p>
                                </div>
                            </div>
                            <span
                                class="text-sm font-bold tabular-nums"
                                :class="c.saldo < 0 ? 'text-rose-600' : 'text-purple-800'"
                            >
                                {{ formatCurrency(c.saldo) }}
                            </span>
                        </li>
                    </ul>
                </Card>

                <!-- Categorias -->
                <Card class="animate-fade-in overflow-hidden shadow-sm" style="animation-delay: 80ms">
                    <div class="border-b border-neutral-100 bg-white px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <ChartColumn :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Gastos por categoria</h3>
                                <p class="text-xs text-neutral-500">{{ mesActual }}</p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="gastosPorCategoria.length === 0"
                        class="flex h-56 flex-col items-center justify-center gap-2 text-sm text-neutral-400"
                    >
                        <PiggyBank :size="32" class="text-neutral-200" />
                        Sin gastos este mes.
                    </div>

                    <ul v-else class="space-y-4 p-6">
                        <li
                            v-for="(c, i) in gastosPorCategoria"
                            :key="c.categoria"
                            class="animate-fade-in"
                            :style="{ animationDelay: `${i * 60}ms` }"
                        >
                            <div class="mb-1.5 flex items-center justify-between text-sm">
                                <span class="inline-flex items-center gap-2 font-semibold text-neutral-800">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                        :style="{
                                            backgroundColor: hexToRgba(c.color || '#8b5cf6', 0.15),
                                            color: c.color || '#8b5cf6',
                                            '--tw-ring-color': hexToRgba(c.color || '#8b5cf6', 0.35),
                                        }"
                                    >
                                        <Tag :size="11" />
                                    </span>
                                    {{ c.categoria }}
                                </span>
                                <span class="font-bold tabular-nums text-neutral-900">
                                    {{ formatCurrency(c.total) }}
                                </span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-neutral-100">
                                <div
                                    class="h-full rounded-full transition-all duration-700 ease-out"
                                    :style="{
                                        width: (Number(c.total) / maxCategoria) * 100 + '%',
                                        background: `linear-gradient(90deg, ${c.color || '#8b5cf6'}, ${(c.color || '#8b5cf6')}99)`,
                                    }"
                                />
                            </div>
                        </li>
                    </ul>
                </Card>
            </div>

            <!-- Próximos cobros -->
            <Card
                v-if="proximosCargos.length > 0"
                class="animate-fade-in mt-6 overflow-hidden shadow-sm"
            >
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <Repeat :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Próximos cobros recurrentes</h3>
                                <p class="text-xs text-neutral-500">{{ proximosCargos.length }} pendiente(s)</p>
                            </div>
                        </div>
                        <Link
                            :href="route('recurrentes.index')"
                            class="rounded-lg border border-purple-200 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 transition hover:border-purple-400 hover:bg-purple-50"
                        >
                            Ver todos
                        </Link>
                    </div>
                </div>

                <ul class="divide-y divide-neutral-100">
                    <li
                        v-for="(c, i) in proximosCargos"
                        :key="c.id"
                        class="animate-fade-in group flex items-center justify-between gap-3 px-6 py-4 transition-all hover:bg-purple-50/60"
                        :style="{ animationDelay: `${i * 50}ms` }"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <Repeat :size="16" />
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-neutral-900">{{ c.nombre }}</div>
                                <div class="inline-flex items-center gap-1 text-xs text-neutral-500">
                                    <span class="rounded-md border border-purple-200 bg-purple-50 px-1.5 py-0.5 text-[10px] font-semibold text-purple-700">
                                        {{ formatFecha(c.proxima_fecha) }}
                                    </span>
                                    <span>{{ c.cuenta?.nombre }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="text-sm font-bold tabular-nums text-neutral-900">
                                {{ formatCurrency(c.monto) }}
                            </span>
                            <button
                                class="inline-flex items-center gap-1 rounded-lg bg-neutral-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-neutral-800"
                                @click="pagarCargo(c)"
                            >
                                <CheckCircle2 :size="13" /> Pagar
                            </button>
                        </div>
                    </li>
                </ul>
            </Card>

            <!-- Últimos movimientos (mismo estilo que la Vue de Movimientos) -->
            <Card class="animate-fade-in mt-6 overflow-hidden shadow-sm" :padded="false">
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <Wallet :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Últimos movimientos</h3>
                                <p class="text-xs text-neutral-500">Actividad reciente</p>
                            </div>
                        </div>
                        <Link
                            :href="route('movimientos.index')"
                            class="rounded-lg border border-purple-200 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 transition hover:border-purple-400 hover:bg-purple-50"
                        >
                            Ver todos
                        </Link>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-100 bg-neutral-50/80 text-left text-[11px] font-medium uppercase tracking-wider text-neutral-500">
                                <th class="px-6 py-3.5">Tipo</th>
                                <th class="px-6 py-3.5">Fecha</th>
                                <th class="px-6 py-3.5">Cuenta</th>
                                <th class="px-6 py-3.5">Categoría</th>
                                <th class="px-6 py-3.5">Descripción</th>
                                <th class="px-6 py-3.5 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="ultimosMovimientos.length === 0">
                                <td colspan="6" class="px-6 py-14 text-center text-sm text-neutral-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <Wallet :size="30" class="text-neutral-200" />
                                        Aun no tienes movimientos registrados.
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="(m, i) in ultimosMovimientos"
                                :key="m.id"
                                class="animate-fade-in group border-b border-neutral-50 transition-all duration-200 last:border-0 hover:bg-purple-50/60"
                                :class="m.tipo === 'ingreso'
                                    ? 'hover:bg-neutral-50'
                                    : 'hover:bg-neutral-50'"
                                :style="{ animationDelay: `${i * 25}ms` }"
                            >
                                <!-- Icono tipo -->
                                <td class="px-6 py-4">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full"
                                        :class="m.tipo === 'ingreso' ? 'bg-purple-100 text-purple-700' : 'bg-neutral-950 text-white'"
                                    >
                                        <component :is="m.tipo === 'ingreso' ? ArrowUpCircle : ArrowDownCircle" :size="14" />
                                    </span>
                                </td>

                                <!-- Fecha -->
                                <td class="whitespace-nowrap px-6 py-4 text-neutral-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <CalendarDays :size="12" class="text-neutral-400" />
                                        {{ formatFecha(m.fecha) }}
                                    </span>
                                </td>

                                <!-- Cuenta con color de la cuenta -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(m.cuenta?.color, 0.15),
                                                color: m.cuenta?.color || '#7c3aed',
                                                '--tw-ring-color': hexToRgba(m.cuenta?.color, 0.35),
                                            }"
                                        >
                                            <Wallet :size="13" />
                                        </span>
                                        <span class="font-semibold text-neutral-800">{{ m.cuenta?.nombre }}</span>
                                    </span>
                                </td>

                                <!-- Categoria con estilo propio -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="m.categoria"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset"
                                        :style="{
                                            backgroundColor: hexToRgba(m.categoria.color, 0.12),
                                            color: m.categoria.color,
                                            '--tw-ring-color': hexToRgba(m.categoria.color, 0.35),
                                        }"
                                    >
                                        <Tag :size="11" />
                                        {{ m.categoria.nombre }}
                                    </span>
                                    <span v-else class="text-neutral-300">-</span>
                                </td>

                                <!-- Descripcion -->
                                <td class="px-6 py-4 text-neutral-500">{{ m.descripcion || '-' }}</td>

                                <!-- Monto con gradiente segun tipo -->
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="whitespace-nowrap text-sm font-semibold tabular-nums"
                                        :class="m.tipo === 'ingreso' ? 'text-purple-600' : 'text-neutral-950'"
                                        >
                                        {{ m.tipo === 'ingreso' ? '+' : '-' }} {{ formatCurrency(m.monto) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

        </div>
    </AuthenticatedLayout>
</template>