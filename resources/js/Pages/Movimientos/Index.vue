<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CalculadoraGastos from '@/Components/CalculadoraGastos.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar, advertencia } from '@/lib/alertas';
import { ICONOS_CUENTA, ETIQUETAS_CUENTA, iconoCategoria } from '@/lib/iconos';
import {
    Plus, ArrowLeftRight, Pencil, Trash2, Filter, X, ArrowUpCircle,
    ArrowDownCircle, Scale, Wallet, Tags, Tag, CalendarDays, ListFilter,
    FileText, Coins, Receipt, Sparkles, Check, ArrowRight, Landmark,
    ChevronLeft, ChevronRight, TrendingUp, TrendingDown, PiggyBank,
    CalendarRange, Undo2, Calculator, CreditCard, Briefcase, Repeat, Lock, Minus,
} from '@lucide/vue';

const ORIGENES_BLOQUEADOS = ['abono_deuda', 'aumento_deuda', 'pago_proyecto'];

// Etiqueta para los movimientos que se generaron desde otra sección
const ORIGENES = {
    abono_deuda: { label: 'Abono a deuda', icon: CreditCard },
    aumento_deuda: { label: 'Deuda', icon: CreditCard },
    pago_proyecto: { label: 'Proyecto', icon: Briefcase },
    cargo_recurrente: { label: 'Recurrente', icon: Repeat },
};

const props = defineProps({
    movimientos: Array,
    transferencias: Array,
    cuentas: Array,
    categorias: Array,
    filtros: Object,
    totales: Object,
});

const tab = ref('movimientos');

// Mes actual
const mesActual = computed(() => {
    const hoy = new Date();
    const y = hoy.getFullYear();
    const m = String(hoy.getMonth() + 1).padStart(2, '0');
    return `${y}-${m}`;
});

// --- filtros ---
const filtros = ref({
    cuenta_id: props.filtros.cuenta_id || '',
    categoria_id: props.filtros.categoria_id || '',
    tipo: props.filtros.tipo || '',
    mes: props.filtros.mes === undefined ? mesActual.value : props.filtros.mes,
});

// ¿Estamos viendo todos los meses (sin filtro de mes)?
const viendoTodosLosMeses = computed(() => filtros.value.mes === '');

// Categorías filtradas según el tipo seleccionado en los filtros
const categoriasFiltro = computed(() => {
    if (!filtros.value.tipo) return props.categorias;
    return props.categorias.filter((c) => c.tipo === filtros.value.tipo);
});

// Si el usuario cambia el tipo y la categoría ya no aplica, se limpia
watch(
    () => filtros.value.tipo,
    () => {
        if (filtros.value.categoria_id) {
            const cat = props.categorias.find((c) => c.id === filtros.value.categoria_id);
            if (cat && filtros.value.tipo && cat.tipo !== filtros.value.tipo) {
                filtros.value.categoria_id = '';
            }
        }
    }
);

const filtrosActivos = computed(() => {
    const activos = [];
    if (filtros.value.cuenta_id) activos.push('cuenta');
    if (filtros.value.categoria_id) activos.push('categoria');
    if (filtros.value.tipo) activos.push('tipo');
    if (filtros.value.mes && filtros.value.mes !== mesActual.value) activos.push('mes');
    return activos.length;
});

// --- KPI 1: Saldo según cuenta seleccionada ---
const cuentaFiltroSeleccionada = computed(() =>
    props.cuentas.find((c) => c.id === filtros.value.cuenta_id) || null
);

const saldoTotalCuentas = computed(() =>
    props.cuentas
        .filter((c) => c.activa)
        .reduce((acc, c) => acc + Number(c.saldo || 0), 0)
);

const saldoMostrado = computed(() => {
    if (cuentaFiltroSeleccionada.value) {
        return Number(cuentaFiltroSeleccionada.value.saldo || 0);
    }
    return saldoTotalCuentas.value;
});

const kpi1Color = computed(() =>
    cuentaFiltroSeleccionada.value?.color || '#7e22ce'
);

const kpi1Titulo = computed(() => {
    if (cuentaFiltroSeleccionada.value) {
        return `Saldo · ${cuentaFiltroSeleccionada.value.nombre}`;
    }
    return 'Saldo total';
});

const kpi1Subtitulo = computed(() => {
    if (cuentaFiltroSeleccionada.value) {
        return `Cuenta: ${cuentaFiltroSeleccionada.value.nombre}`;
    }
    return `${props.cuentas.filter((c) => c.activa).length} cuenta(s) activa(s)`;
});

// Debounce
let debounceTimer = null;
watch(
    filtros,
    () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            router.get(route('movimientos.index'), filtros.value, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300);
    },
    { deep: true }
);

function limpiarFiltros() {
    filtros.value = {
        cuenta_id: '',
        categoria_id: '',
        tipo: '',
        mes: mesActual.value,
    };
}

// Ver TODOS los meses: quita el filtro de mes
function verTodosLosMeses() {
    filtros.value.mes = '';
}

// Volver al mes actual
function volverAlMesActual() {
    filtros.value.mes = mesActual.value;
}

function cambiarMes(delta) {
    // Si estamos viendo todos, al navegar por meses pasamos al mes actual primero
    if (!filtros.value.mes) {
        filtros.value.mes = mesActual.value;
        return;
    }
    const [y, m] = filtros.value.mes.split('-').map(Number);
    const d = new Date(y, m - 1 + delta, 1);
    const newY = d.getFullYear();
    const newM = String(d.getMonth() + 1).padStart(2, '0');
    filtros.value.mes = `${newY}-${newM}`;
}

const etiquetaMes = computed(() => {
    if (viendoTodosLosMeses.value) return 'Todos los meses';
    if (!filtros.value.mes) return '';
    const [y, m] = filtros.value.mes.split('-').map(Number);
    const d = new Date(y, m - 1, 1);
    const nombre = d.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
    return nombre.charAt(0).toUpperCase() + nombre.slice(1);
});

// --- tabla: monto separado en enteros y centavos ---
function partesMonto(monto) {
    const [enteros, centavos = '00'] = formatCurrency(monto).split('.');
    return { enteros, centavos };
}

// --- tabla: movimientos agrupados por día ---
function etiquetaDia(fecha) {
    if (fecha === hoyIso()) return 'Hoy';
    const ayer = new Date();
    ayer.setDate(ayer.getDate() - 1);
    const [y, m, d] = fecha.split('-').map(Number);
    if (ayer.getFullYear() === y && ayer.getMonth() + 1 === m && ayer.getDate() === d) return 'Ayer';
    const texto = new Date(y, m - 1, d).toLocaleDateString('es-MX', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        ...(y !== new Date().getFullYear() && { year: 'numeric' }),
    });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

const gruposMovimientos = computed(() => {
    const grupos = [];
    let indice = 0;
    for (const m of props.movimientos.data) {
        const fecha = m.fecha.slice(0, 10);
        let grupo = grupos[grupos.length - 1];
        if (!grupo || grupo.fecha !== fecha) {
            grupo = { fecha, etiqueta: etiquetaDia(fecha), neto: 0, items: [] };
            grupos.push(grupo);
        }
        grupo.neto += (m.tipo === 'ingreso' ? 1 : -1) * Number(m.monto);
        grupo.items.push({ ...m, indice: indice++ });
    }
    return grupos;
});

// --- modal movimiento ---
const showModal = ref(false);
const editando = ref(null);
const form = useForm({
    tipo: 'gasto',
    cuenta_id: '',
    categoria_id: '',
    monto: '',
    fecha: hoyIso(),
    descripcion: '',
});

const categoriasFiltradas = computed(() => props.categorias.filter((c) => c.tipo === form.tipo));

// No se permiten fechas futuras (el servidor también lo valida).
const hoy = hoyIso();

const mostrarCalculadora = ref(false);

function usarCalculadora({ total, descripcion }) {
    form.monto = total;
    if (!form.descripcion) form.descripcion = descripcion;
    mostrarCalculadora.value = false;
}

const cuentaSeleccionada = computed(() =>
    props.cuentas.find((c) => c.id === form.cuenta_id)
);

const saldoProyectado = computed(() => {
    if (!cuentaSeleccionada.value || !form.monto) return null;
    const monto = Number(form.monto);
    if (!monto || monto <= 0) return null;
    const base = Number(cuentaSeleccionada.value.saldo);
    return form.tipo === 'ingreso' ? base + monto : base - monto;
});

function abrirCrear() {
    editando.value = null;
    form.tipo = 'gasto';
    form.cuenta_id = props.cuentas.find((c) => c.activa)?.id || '';
    form.categoria_id = '';
    form.monto = '';
    form.fecha = hoyIso();
    form.descripcion = '';
    form.clearErrors();
    mostrarCalculadora.value = false;
    showModal.value = true;
}

function abrirEditar(m) {
    if (ORIGENES_BLOQUEADOS.includes(m.origen)) {
        advertencia('Este movimiento se generó automáticamente desde una deuda o proyecto. Edítalo desde esa sección.');
        return;
    }
    editando.value = m;
    form.tipo = m.tipo;
    form.cuenta_id = m.cuenta_id;
    form.categoria_id = m.categoria_id || '';
    form.monto = m.monto;
    form.fecha = m.fecha.slice(0, 10);
    form.descripcion = m.descripcion || '';
    form.clearErrors();
    mostrarCalculadora.value = false;
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('movimientos.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('movimientos.store'), { onSuccess: () => (showModal.value = false) });
    }
}

function eliminarMovimiento(m) {
    confirmar({
        title: 'Eliminar movimiento',
        content: `Se eliminará «${m.descripcion || (m.tipo === 'ingreso' ? 'Ingreso' : 'Gasto')}» por ${formatCurrency(m.monto)}. No se puede deshacer.`,
        onOk: () => router.delete(route('movimientos.destroy', m.id)),
    });
}

// --- modal transferencia ---
const showTransferModal = ref(false);
const transferForm = useForm({
    cuenta_origen_id: '',
    cuenta_destino_id: '',
    monto: '',
    fecha: hoyIso(),
    descripcion: '',
});

function abrirTransferencia() {
    transferForm.cuenta_origen_id = props.cuentas.find((c) => c.activa)?.id || '';
    transferForm.cuenta_destino_id = props.cuentas.filter((c) => c.activa)[1]?.id || '';
    transferForm.monto = '';
    transferForm.fecha = hoyIso();
    transferForm.descripcion = '';
    transferForm.clearErrors();
    showTransferModal.value = true;
}

function guardarTransferencia() {
    transferForm.post(route('transferencias.store'), { onSuccess: () => (showTransferModal.value = false) });
}

function eliminarTransferencia(t) {
    confirmar({
        title: 'Eliminar transferencia',
        content: `Se eliminará la transferencia de ${formatCurrency(t.monto)}. No se puede deshacer.`,
        onOk: () => router.delete(route('transferencias.destroy', t.id)),
    });
}

const cuentaOrigenT = computed(() => props.cuentas.find((c) => c.id === transferForm.cuenta_origen_id));
const cuentaDestinoT = computed(() => props.cuentas.find((c) => c.id === transferForm.cuenta_destino_id));
const mismaCuenta = computed(
    () => transferForm.cuenta_origen_id && transferForm.cuenta_origen_id === transferForm.cuenta_destino_id
);
const fondosInsuficientesT = computed(
    () => cuentaOrigenT.value && Number(transferForm.monto) > cuentaOrigenT.value.saldo
);

// --- helpers de color ---
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
    <Head title="Movimientos" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <Receipt :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Movimientos
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Ingresos, gastos y transferencias entre cuentas
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <SecondaryButton
                        @click="abrirTransferencia"
                    >
                        <ArrowLeftRight :size="15" /> Transferir
                    </SecondaryButton>
                    <SecondaryButton
                        title="Junta varias compras en un solo gasto"
                        @click="abrirCrear(); mostrarCalculadora = true"
                    >
                        <Calculator :size="15" /> Calcular compra
                    </SecondaryButton>
                    <PrimaryButton
                        @click="abrirCrear"
                    >
                        <Plus :size="16" /> Nuevo movimiento
                    </PrimaryButton>
                </div>
            </div>

            <!-- Totales -->
            <div class="stagger mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Saldo según cuenta seleccionada (o total si no hay) -->
                <div class="card-lift group flex items-center gap-4 rounded-2xl border border-neutral-200/80 bg-white p-5 shadow-card">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-md transition-transform duration-300 group-hover:-rotate-6 group-hover:scale-110"
                        :style="{ background: `linear-gradient(135deg, ${kpi1Color}, #0a0a0a)` }"
                    >
                        <component :is="cuentaFiltroSeleccionada ? Wallet : PiggyBank" :size="18" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-xs font-medium text-neutral-500" :title="kpi1Titulo">{{ kpi1Titulo }}</div>
                        <div class="mt-0.5 text-xl font-semibold tabular-nums text-neutral-900">{{ formatCurrency(saldoMostrado) }}</div>
                        <div class="text-[11px] text-neutral-400">{{ kpi1Subtitulo }}</div>
                    </div>
                </div>

                <!-- Gastos -->
                <div class="card-lift group flex items-center gap-4 rounded-2xl border border-neutral-200/80 bg-white p-5 shadow-card">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-neutral-950 text-white shadow-md transition-transform duration-300 group-hover:-rotate-6 group-hover:scale-110">
                        <ArrowDownCircle :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-medium text-neutral-500">Gastos</div>
                        <div class="mt-0.5 text-xl font-semibold tabular-nums text-neutral-900">{{ formatCurrency(totales.gastos) }}</div>
                        <div class="text-[11px] text-neutral-400">
                            {{ viendoTodosLosMeses ? 'En todos los meses' : `En ${etiquetaMes}` }}
                        </div>
                    </div>
                </div>

                <!-- Balance -->
                <div class="card-lift group flex items-center gap-4 rounded-2xl border border-neutral-200/80 bg-white p-5 shadow-card">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                        :class="(totales.ingresos - totales.gastos) < 0 ? 'bg-rose-50 text-rose-600' : 'bg-gradient-to-br from-purple-500 to-purple-800 text-white shadow-md'"
                    >
                        <Scale :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-medium text-neutral-500">
                            {{ viendoTodosLosMeses ? 'Balance global' : 'Balance del mes' }}
                        </div>
                        <div
                            class="mt-0.5 text-xl font-semibold tabular-nums"
                            :class="(totales.ingresos - totales.gastos) < 0 ? 'text-rose-600' : 'text-neutral-900'"
                        >
                            {{ formatCurrency(totales.ingresos - totales.gastos) }}
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-neutral-400">
                            <span class="inline-flex items-center gap-0.5">
                                <ArrowUpCircle :size="10" class="text-purple-600" />
                                {{ formatCurrency(totales.ingresos) }}
                            </span>
                            <span class="text-neutral-300">·</span>
                            <span class="inline-flex items-center gap-0.5">
                                <ArrowDownCircle :size="10" class="text-neutral-900" />
                                {{ formatCurrency(totales.gastos) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros con tabs integrados -->
            <Card class="animate-fade-in mb-5 overflow-hidden shadow-sm" :padded="false">

                <!-- Header con tabs integrados -->
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Tabs -->
                        <div class="inline-flex w-full items-center gap-1 rounded-xl border border-neutral-200 bg-white p-1 shadow-sm sm:w-auto">
                            <button
                                class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 sm:flex-initial"
                                :class="tab === 'movimientos'
                                    ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                                    : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                                @click="tab = 'movimientos'"
                            >
                                <Receipt :size="14" />
                                Movimientos
                                <span
                                    class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                                    :class="tab === 'movimientos' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                                >
                                    {{ movimientos.total ?? movimientos.data.length }}
                                </span>
                            </button>
                            <button
                                class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 sm:flex-initial"
                                :class="tab === 'transferencias'
                                    ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                                    : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                                @click="tab = 'transferencias'"
                            >
                                <ArrowLeftRight :size="14" />
                                Transferencias
                                <span
                                    class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                                    :class="tab === 'transferencias' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                                >
                                    {{ transferencias.total ?? transferencias.data.length }}
                                </span>
                            </button>
                        </div>

                        <!-- Info filtros + limpiar -->
                        <div class="flex items-center gap-2">
                            <!-- Chip "Viendo todos los meses" -->
                            <span
                                v-if="viendoTodosLosMeses"
                                class="inline-flex items-center gap-1 rounded-lg border border-purple-300 bg-purple-100 px-2.5 py-1.5 text-[11px] font-bold text-purple-800 shadow-sm"
                            >
                                <CalendarRange :size="12" />
                                Viendo todos los meses
                            </span>

                            <div class="flex items-center gap-2 text-[11px] text-purple-700">
                                <Filter :size="12" />
                                {{ filtrosActivos > 0 ? `${filtrosActivos} filtro(s) activo(s)` : 'Se aplican automaticamente' }}
                            </div>
                            <button
                                v-if="filtrosActivos > 0"
                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100"
                                @click="limpiarFiltros"
                            >
                                <X :size="12" /> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <!-- Selector de mes destacado -->
                    <div
                        class="mb-5 flex flex-col gap-3 rounded-xl border p-4 transition-all duration-300 sm:flex-row sm:items-center sm:justify-between"
                        :class="viendoTodosLosMeses
                            ? 'border-purple-300 bg-white shadow-sm'
                            : 'border-purple-200 bg-white'"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm"
                                :class="viendoTodosLosMeses
                                    ? 'bg-purple-800'
                                    : 'bg-purple-600'"
                            >
                                <component :is="viendoTodosLosMeses ? CalendarRange : CalendarDays" :size="18" />
                            </span>
                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-wider"
                                    :class="viendoTodosLosMeses ? 'text-purple-800' : 'text-purple-700'"
                                >
                                    {{ viendoTodosLosMeses ? 'Periodo' : 'Mes consultado' }}
                                </p>
                                <p class="text-base font-bold text-neutral-900">
                                    {{ etiquetaMes }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Si NO estamos viendo todos: mostrar navegación normal + botón "Ver todos" -->
                            <template v-if="!viendoTodosLosMeses">
                                <button
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-purple-200 bg-white text-purple-700 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-md"
                                    title="Mes anterior"
                                    @click="cambiarMes(-1)"
                                >
                                    <ChevronLeft :size="16" />
                                </button>

                                <input
                                    v-model="filtros.mes"
                                    type="month"
                                    class="rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm font-semibold text-purple-800 shadow-sm outline-none transition focus:border-purple-400 focus:ring-2 focus:ring-purple-200"
                                />

                                <button
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-purple-200 bg-white text-purple-700 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-md disabled:pointer-events-none disabled:opacity-40"
                                    title="Mes siguiente"
                                    :disabled="filtros.mes >= mesActual"
                                    @click="cambiarMes(1)"
                                >
                                    <ChevronRight :size="16" />
                                </button>

                                <button
                                    v-if="filtros.mes !== mesActual"
                                    class="rounded-lg border border-purple-200 bg-purple-50 px-3 py-2 text-xs font-semibold text-purple-800 transition-all duration-200 hover:bg-purple-100 hover:shadow-sm"
                                    @click="filtros.mes = mesActual"
                                >
                                    Ir al mes actual
                                </button>

                                <!-- Botón Ver todos los meses -->
                                <button
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-purple-700 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-purple-600 hover:shadow-md"
                                    title="Ver movimientos de todos los meses"
                                    @click="verTodosLosMeses"
                                >
                                    <CalendarRange :size="13" />
                                    Ver todos los meses
                                </button>
                            </template>

                            <!-- Si SÍ estamos viendo todos: botón para volver al mes actual -->
                            <template v-else>
                                <button
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-purple-300 bg-white px-3 py-2 text-xs font-semibold text-purple-800 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-md"
                                    title="Volver al mes actual"
                                    @click="volverAlMesActual"
                                >
                                    <Undo2 :size="13" />
                                    Volver al mes actual
                                </button>

                                <input
                                    v-model="filtros.mes"
                                    type="month"
                                    class="rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm font-semibold text-purple-800 shadow-sm outline-none transition focus:border-purple-400 focus:ring-2 focus:ring-purple-200"
                                    title="Elige un mes especifico"
                                />
                            </template>
                        </div>
                    </div>

                    <!-- Resto de filtros: Tipo, Cuenta, Categoria -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel value="Tipo" />
                            <SelectInput v-model="filtros.tipo" class="mt-1.5">
                                <option value="">Todos</option>
                                <option value="ingreso">Ingresos</option>
                                <option value="gasto">Gastos</option>
                            </SelectInput>
                        </div>
                        <div>
                            <InputLabel value="Cuenta" />
                            <SelectInput v-model="filtros.cuenta_id" class="mt-1.5">
                                <option value="">Todas</option>
                                <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </SelectInput>
                        </div>
                        <div>
                            <InputLabel value="Categoría" />
                            <SelectInput v-model="filtros.categoria_id" class="mt-1.5">
                                <option value="">Todas</option>
                                <option v-for="c in categoriasFiltro" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </SelectInput>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Tabla movimientos -->
            <Card v-if="tab === 'movimientos'" class="animate-fade-in overflow-hidden shadow-sm" :padded="false">
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <Receipt :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Listado de movimientos</h3>
                                <p class="text-xs text-neutral-500">
                                    {{ movimientos.data.length }} movimiento(s) en esta página
                                    <span v-if="viendoTodosLosMeses" class="font-semibold text-purple-800"> · todos los meses</span>
                                </p>
                            </div>
                        </div>

                        <!-- Botón: si NO está activo, invita a ver todos; si YA está activo, vuelve al mes -->
                        <button
                            v-if="!viendoTodosLosMeses"
                            class="group inline-flex items-center gap-1.5 self-start rounded-lg border border-purple-200 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-md sm:self-auto"
                            title="Ver movimientos de todos los meses"
                            @click="verTodosLosMeses"
                        >
                            <ListFilter :size="13" class="transition-transform duration-200" />
                            Ver todos
                            <ArrowRight :size="12" class="transition-transform duration-200" />
                        </button>
                        <button
                            v-else
                            class="group inline-flex items-center gap-1.5 self-start rounded-lg border border-purple-300 bg-purple-100 px-3 py-1.5 text-xs font-semibold text-purple-800 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-200 hover:shadow-md sm:self-auto"
                            title="Volver al mes actual"
                            @click="volverAlMesActual"
                        >
                            <Undo2 :size="13" class="transition-transform duration-200" />
                            Volver al mes actual
                        </button>
                    </div>
                </div>

                <!-- Vacío -->
                <div v-if="movimientos.data.length === 0" class="flex flex-col items-center gap-3 px-6 py-16 text-center">
                    <span class="flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                        <Receipt :size="24" />
                    </span>
                    <p class="text-sm font-semibold text-neutral-800">
                        No hay movimientos {{ viendoTodosLosMeses ? 'registrados' : `en ${etiquetaMes}` }}
                    </p>
                    <button
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-700 transition hover:text-purple-900"
                        @click="abrirCrear"
                    >
                        <Plus :size="13" /> Registrar el primero
                    </button>
                </div>

                <!-- Escritorio: tabla agrupada por día -->
                <div v-else class="hidden overflow-x-auto md:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                                <th class="py-3 pl-6 pr-3">Concepto</th>
                                <th class="px-3 py-3">Categoría</th>
                                <th class="px-3 py-3">Cuenta</th>
                                <th class="px-3 py-3 text-right">Monto</th>
                                <th class="py-3 pl-3 pr-6"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody v-for="grupo in gruposMovimientos" :key="grupo.fecha">
                            <!-- Encabezado del día -->
                            <tr>
                                <td colspan="5" class="border-y border-neutral-100 bg-neutral-50/80 px-6 py-2">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center gap-2 text-xs font-bold text-neutral-800">
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="grupo.etiqueta === 'Hoy' ? 'animate-pulse bg-purple-500 shadow-[0_0_6px_2px_rgba(168,85,247,0.5)]' : 'bg-neutral-300'"
                                            ></span>
                                            {{ grupo.etiqueta }}
                                            <span class="font-medium text-neutral-400">{{ formatFecha(grupo.fecha) }}</span>
                                        </span>
                                        <span
                                            class="text-xs font-semibold tabular-nums"
                                            :class="grupo.neto >= 0 ? 'text-purple-600' : 'text-neutral-500'"
                                        >
                                            {{ grupo.neto >= 0 ? '+' : '−' }}{{ formatCurrency(Math.abs(grupo.neto)) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-for="m in grupo.items"
                                :key="m.id"
                                class="group relative animate-fade-in border-b border-neutral-100/70 transition-colors duration-200 last:border-0 hover:bg-purple-50/50"
                                :style="{ animationDelay: `${m.indice * 35}ms` }"
                            >
                                <!-- Concepto: tipo + descripción + origen -->
                                <td class="relative py-3.5 pl-6 pr-3">
                                    <span
                                        class="absolute inset-y-2 left-0 w-1 origin-center scale-y-0 rounded-r-full transition-transform duration-300 group-hover:scale-y-100"
                                        :class="m.tipo === 'ingreso' ? 'bg-purple-500' : 'bg-neutral-950'"
                                    ></span>
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300 group-hover:-rotate-6 group-hover:scale-110"
                                            :class="m.tipo === 'ingreso'
                                                ? 'bg-gradient-to-br from-purple-500 to-purple-700 text-white shadow-purple-900/30'
                                                : 'bg-neutral-950 text-white'"
                                        >
                                            <component :is="m.tipo === 'ingreso' ? TrendingUp : TrendingDown" :size="16" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="max-w-[22rem] truncate font-semibold text-neutral-900" :title="m.descripcion">
                                                {{ m.descripcion || (m.tipo === 'ingreso' ? 'Ingreso' : 'Gasto') }}
                                            </p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-neutral-400">
                                                {{ m.tipo === 'ingreso' ? 'Ingreso' : 'Gasto' }}
                                                <template v-if="ORIGENES[m.origen]">
                                                    <span class="text-neutral-300">·</span>
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-neutral-100 px-1.5 py-0.5 font-semibold text-neutral-600">
                                                        <component :is="ORIGENES[m.origen].icon" :size="10" />
                                                        {{ ORIGENES[m.origen].label }}
                                                    </span>
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Categoría: icono propio sobre su color -->
                                <td class="px-3 py-3.5">
                                    <span
                                        v-if="m.categoria"
                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border py-1 pl-1 pr-3 text-xs font-semibold transition-all duration-300 group-hover:shadow-sm"
                                        :style="{
                                            backgroundColor: hexToRgba(m.categoria.color, 0.07),
                                            borderColor: hexToRgba(m.categoria.color, 0.25),
                                            color: m.categoria.color,
                                        }"
                                    >
                                        <span
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-white shadow-sm transition-transform duration-300 group-hover:rotate-12"
                                            :style="{ background: `linear-gradient(135deg, ${m.categoria.color}, ${hexToRgba(m.categoria.color, 0.7)})` }"
                                        >
                                            <component :is="iconoCategoria(m.categoria.nombre)" :size="12" stroke-width="2.4" />
                                        </span>
                                        {{ m.categoria.nombre }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border border-dashed border-neutral-200 py-1 pl-1 pr-3 text-xs font-medium text-neutral-400"
                                    >
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-neutral-100">
                                            <Tag :size="11" />
                                        </span>
                                        Sin categoría
                                    </span>
                                </td>

                                <!-- Cuenta: icono según el tipo + etiqueta del tipo -->
                                <td class="px-3 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(m.cuenta.color, 0.1),
                                                color: m.cuenta.color,
                                                '--tw-ring-color': hexToRgba(m.cuenta.color, 0.25),
                                            }"
                                        >
                                            <component :is="ICONOS_CUENTA[m.cuenta.tipo] || Wallet" :size="16" />
                                            <span
                                                class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-white"
                                                :style="{ backgroundColor: m.cuenta.color }"
                                            ></span>
                                        </span>
                                        <div class="leading-tight">
                                            <p class="whitespace-nowrap text-[13px] font-semibold text-neutral-800">{{ m.cuenta.nombre }}</p>
                                            <p class="text-[10px] font-medium uppercase tracking-wider text-neutral-400">
                                                {{ ETIQUETAS_CUENTA[m.cuenta.tipo] || 'Cuenta' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Monto: enteros grandes, centavos pequeños -->
                                <td class="px-3 py-3.5 text-right">
                                    <div
                                        class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 transition-all duration-300 group-hover:scale-105"
                                        :class="m.tipo === 'ingreso'
                                            ? 'bg-gradient-to-br from-purple-50 to-purple-100/70 ring-1 ring-inset ring-purple-200'
                                            : 'bg-neutral-950 shadow-md shadow-neutral-900/20'"
                                    >
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full"
                                            :class="m.tipo === 'ingreso' ? 'bg-purple-600 text-white' : 'bg-white/15 text-white'"
                                        >
                                            <component :is="m.tipo === 'ingreso' ? Plus : Minus" :size="11" stroke-width="3" />
                                        </span>
                                        <span
                                            class="whitespace-nowrap font-bold tabular-nums"
                                            :class="m.tipo === 'ingreso' ? 'text-purple-800' : 'text-white'"
                                        >
                                            <span class="text-[15px]">{{ partesMonto(m.monto).enteros }}</span><span
                                                class="text-[11px]"
                                                :class="m.tipo === 'ingreso' ? 'text-purple-500' : 'text-neutral-400'"
                                            >.{{ partesMonto(m.monto).centavos }}</span>
                                        </span>
                                    </div>
                                </td>

                                <td class="py-3.5 pl-3 pr-6">
                                    <div class="flex justify-end gap-1 opacity-40 transition-opacity duration-200 group-hover:opacity-100">
                                        <button
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-neutral-500 transition-all duration-200 hover:bg-purple-600 hover:text-white hover:shadow-md hover:shadow-purple-900/30 active:scale-90"
                                            :title="ORIGENES_BLOQUEADOS.includes(m.origen) ? 'Se edita desde su sección' : 'Editar'"
                                            @click="abrirEditar(m)"
                                        >
                                            <component :is="ORIGENES_BLOQUEADOS.includes(m.origen) ? Lock : Pencil" :size="15" />
                                        </button>
                                        <button
                                            v-if="!ORIGENES_BLOQUEADOS.includes(m.origen)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-neutral-500 transition-all duration-200 hover:bg-rose-600 hover:text-white hover:shadow-md hover:shadow-rose-900/30 active:scale-90"
                                            title="Eliminar"
                                            @click="eliminarMovimiento(m)"
                                        >
                                            <Trash2 :size="15" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Celular: tarjetas agrupadas por día -->
                <div v-if="movimientos.data.length" class="md:hidden">
                    <div v-for="grupo in gruposMovimientos" :key="grupo.fecha">
                        <div class="flex items-center justify-between border-y border-neutral-100 bg-neutral-50/80 px-4 py-2 text-xs">
                            <span class="font-bold text-neutral-800">{{ grupo.etiqueta }}</span>
                            <span class="font-semibold tabular-nums" :class="grupo.neto >= 0 ? 'text-purple-600' : 'text-neutral-500'">
                                {{ grupo.neto >= 0 ? '+' : '−' }}{{ formatCurrency(Math.abs(grupo.neto)) }}
                            </span>
                        </div>
                        <div
                            v-for="m in grupo.items"
                            :key="m.id"
                            class="flex animate-fade-in items-center gap-3 border-b border-neutral-100/70 px-4 py-3 last:border-0 active:bg-purple-50"
                            :style="{ animationDelay: `${m.indice * 35}ms` }"
                            @click="abrirEditar(m)"
                        >
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white"
                                :class="m.tipo === 'ingreso' ? 'bg-gradient-to-br from-purple-500 to-purple-700' : 'bg-neutral-950'"
                            >
                                <component :is="m.tipo === 'ingreso' ? TrendingUp : TrendingDown" :size="16" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-neutral-900">
                                    {{ m.descripcion || (m.tipo === 'ingreso' ? 'Ingreso' : 'Gasto') }}
                                </p>
                                <p class="mt-1 flex items-center gap-1.5 truncate text-[11px] text-neutral-500">
                                    <span
                                        v-if="m.categoria"
                                        class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 font-semibold"
                                        :style="{ backgroundColor: hexToRgba(m.categoria.color, 0.1), color: m.categoria.color }"
                                    >
                                        <component :is="iconoCategoria(m.categoria.nombre)" :size="10" stroke-width="2.4" />
                                        {{ m.categoria.nombre }}
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <component :is="ICONOS_CUENTA[m.cuenta.tipo] || Wallet" :size="10" :style="{ color: m.cuenta.color }" />
                                        {{ m.cuenta.nombre }}
                                    </span>
                                </p>
                            </div>
                            <span
                                class="whitespace-nowrap rounded-lg px-2 py-1 font-bold tabular-nums"
                                :class="m.tipo === 'ingreso' ? 'bg-purple-50 text-purple-800' : 'bg-neutral-950 text-white'"
                            >
                                <span class="text-sm">{{ m.tipo === 'ingreso' ? '+' : '−' }}{{ partesMonto(m.monto).enteros }}</span><span
                                    class="text-[10px] opacity-60"
                                >.{{ partesMonto(m.monto).centavos }}</span>
                            </span>
                        </div>
                    </div>
                </div>
                <Pagination :paginator="movimientos" />
            </Card>

            <!-- Tabla transferencias -->
            <Card v-else class="animate-fade-in overflow-hidden shadow-sm" :padded="false">
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <ArrowLeftRight :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Transferencias entre cuentas</h3>
                                <p class="text-xs text-neutral-500">
                                    {{ transferencias.data.length }} transferencia(s) en esta página
                                    <span v-if="viendoTodosLosMeses" class="font-semibold text-purple-800"> · todos los meses</span>
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="!viendoTodosLosMeses"
                            class="group inline-flex items-center gap-1.5 self-start rounded-lg border border-purple-200 bg-white px-3 py-1.5 text-xs font-semibold text-purple-700 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 hover:shadow-md sm:self-auto"
                            title="Ver transferencias de todos los meses"
                            @click="verTodosLosMeses"
                        >
                            <ListFilter :size="13" class="transition-transform duration-200" />
                            Ver todas
                            <ArrowRight :size="12" class="transition-transform duration-200" />
                        </button>
                        <button
                            v-else
                            class="group inline-flex items-center gap-1.5 self-start rounded-lg border border-purple-300 bg-purple-100 px-3 py-1.5 text-xs font-semibold text-purple-800 shadow-sm transition-all duration-200 hover:border-purple-400 hover:bg-purple-200 hover:shadow-md sm:self-auto"
                            title="Volver al mes actual"
                            @click="volverAlMesActual"
                        >
                            <Undo2 :size="13" class="transition-transform duration-200" />
                            Volver al mes actual
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-100 bg-neutral-50/80 text-left text-[11px] font-medium uppercase tracking-wider text-neutral-500">
                                <th class="px-6 py-3.5">Fecha</th>
                                <th class="px-6 py-3.5">Origen</th>
                                <th class="px-6 py-3.5"></th>
                                <th class="px-6 py-3.5">Destino</th>
                                <th class="px-6 py-3.5">Descripción</th>
                                <th class="px-6 py-3.5 text-right">Monto</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="transferencias.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center text-sm text-neutral-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <ArrowLeftRight :size="32" class="text-neutral-200" />
                                        Aun no has hecho transferencias {{ viendoTodosLosMeses ? '' : `en ${etiquetaMes}` }}.
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="(t, i) in transferencias.data"
                                :key="t.id"
                                class="animate-fade-in group border-b border-neutral-50 transition-all duration-200 last:border-0 hover:bg-purple-50/60"
                                :style="{ animationDelay: `${i * 25}ms` }"
                            >
                                <td class="whitespace-nowrap px-6 py-4 text-neutral-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <CalendarDays :size="12" class="text-neutral-400" />
                                        {{ formatFecha(t.fecha) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(t.cuenta_origen.color, 0.15),
                                                color: t.cuenta_origen.color,
                                                '--tw-ring-color': hexToRgba(t.cuenta_origen.color, 0.35),
                                            }"
                                        >
                                            <Landmark :size="13" />
                                        </span>
                                        <span class="font-semibold text-neutral-800">{{ t.cuenta_origen.nombre }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-purple-100 text-purple-700">
                                        <ArrowRight :size="14" />
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(t.cuenta_destino.color, 0.15),
                                                color: t.cuenta_destino.color,
                                                '--tw-ring-color': hexToRgba(t.cuenta_destino.color, 0.35),
                                            }"
                                        >
                                            <Landmark :size="13" />
                                        </span>
                                        <span class="font-semibold text-neutral-800">{{ t.cuenta_destino.nombre }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-neutral-500">{{ t.descripcion || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-purple-500 px-3 py-1.5 text-xs font-bold tabular-nums text-white shadow-sm transition-transform"
                                    >
                                        <ArrowLeftRight :size="12" />
                                        {{ formatCurrency(t.monto) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="relative inline-block">
                                        <button
                                            class="peer group/btn relative flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-rose-200 bg-white text-rose-600 shadow-sm transition-all duration-300 hover:border-rose-400 hover:bg-rose-500 hover:text-white hover:shadow-md"
                                            title="Eliminar transferencia"
                                            @click="eliminarTransferencia(t)"
                                        >
                                            <Trash2 :size="16" class="relative transition-transform duration-300" />
                                        </button>
                                        <span
                                            class="pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg border border-rose-800 bg-rose-800 px-2.5 py-1 text-[10px] font-bold text-white opacity-0 shadow-lg transition-all duration-200 peer-hover:-top-8 peer-hover:opacity-100"
                                        >
                                            Eliminar
                                            <span class="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 border-r border-b border-rose-800 bg-rose-950"></span>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :paginator="transferencias" />
            </Card>
        </div>

        <!-- MODAL movimiento -->
        <Modal :show="showModal" max-width="2xl" @close="showModal = false">
            <div class="overflow-hidden rounded-xl bg-white">
                <div
                    class="relative overflow-hidden border-b px-8 py-6 text-neutral-900"
                    :class="form.tipo === 'ingreso'
                        ? 'border-neutral-200 bg-white'
                        : 'border-neutral-200 bg-white'"
                >

                    <div class="relative flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl transition-colors duration-300"
                            :class="form.tipo === 'ingreso'
                                ? 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200'
                                : 'bg-neutral-950 text-white ring-1 ring-inset ring-neutral-800'"
                        >
                            <component :is="form.tipo === 'ingreso' ? ArrowUpCircle : ArrowDownCircle" :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">
                                {{ editando ? 'Editar movimiento' : 'Nuevo movimiento' }}
                            </h2>
                            <p class="text-xs text-neutral-500">
                                {{ form.tipo === 'ingreso' ? 'Registra un ingreso en una cuenta' : 'Registra un gasto en una cuenta' }}
                            </p>
                        </div>
                    </div>
                </div>

                <form class="p-8" @submit.prevent="guardar">
                    <div class="space-y-6">
                        <div>
                            <InputLabel value="Tipo de movimiento" />
                            <div class="mt-2 inline-flex w-full rounded-xl border border-neutral-200 bg-neutral-50 p-1.5">
                                <button
                                    type="button"
                                    class="relative flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-bold transition-all duration-300"
                                    :class="form.tipo === 'gasto'
                                        ? 'bg-neutral-950 text-white shadow-md shadow-neutral-900/30'
                                        : 'text-neutral-500 hover:bg-white hover:text-neutral-800'"
                                    @click="form.tipo = 'gasto'; form.categoria_id = ''"
                                >
                                    <ArrowDownCircle :size="16" />
                                    Gasto
                                </button>
                                <button
                                    type="button"
                                    class="relative flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-bold transition-all duration-300"
                                    :class="form.tipo === 'ingreso'
                                        ? 'bg-gradient-to-br from-purple-600 to-purple-800 text-white shadow-md shadow-purple-900/30'
                                        : 'text-neutral-500 hover:bg-white hover:text-neutral-800'"
                                    @click="form.tipo = 'ingreso'; form.categoria_id = ''"
                                >
                                    <ArrowUpCircle :size="16" />
                                    Ingreso
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Cuenta" />
                                <SelectInput v-model="form.cuenta_id" class="mt-1.5">
                                    <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">
                                        {{ c.nombre }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.cuenta_id" />

                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-1"
                                >
                                    <div
                                        v-if="cuentaSeleccionada"
                                        class="mt-2.5 flex items-center justify-between gap-2 rounded-xl border px-3 py-2 shadow-sm"
                                        :style="{
                                            backgroundColor: hexToRgba(cuentaSeleccionada.color, 0.08),
                                            borderColor: hexToRgba(cuentaSeleccionada.color, 0.3),
                                        }"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                                :style="{
                                                    backgroundColor: hexToRgba(cuentaSeleccionada.color, 0.18),
                                                    color: cuentaSeleccionada.color,
                                                    '--tw-ring-color': hexToRgba(cuentaSeleccionada.color, 0.35),
                                                }"
                                            >
                                                <Wallet :size="13" />
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                                    Saldo actual
                                                </p>
                                                <p
                                                    class="text-sm font-bold tabular-nums"
                                                    :style="{ color: cuentaSeleccionada.color }"
                                                >
                                                    {{ formatCurrency(cuentaSeleccionada.saldo) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="saldoProyectado !== null"
                                            class="flex items-center gap-1 rounded-lg bg-white/70 px-2 py-1 text-[11px] font-bold tabular-nums shadow-sm"
                                            :class="saldoProyectado < 0 ? 'text-rose-600' : 'text-purple-700'"
                                        >
                                            <component :is="saldoProyectado < 0 ? TrendingDown : TrendingUp" :size="11" />
                                            {{ formatCurrency(saldoProyectado) }}
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <div>
                                <InputLabel value="Monto" />
                                <div class="mt-1.5 flex gap-2">
                                    <CurrencyInput v-model="form.monto" class="flex-1" />
                                    <button
                                        type="button"
                                        class="flex h-[38px] shrink-0 items-center gap-1.5 rounded-lg border px-3 text-xs font-semibold shadow-sm transition-all duration-200"
                                        :class="mostrarCalculadora
                                            ? 'border-purple-600 bg-purple-600 text-white'
                                            : 'border-purple-200 bg-purple-50 text-purple-800 hover:bg-purple-100'"
                                        title="Sumar varias compras"
                                        @click="mostrarCalculadora = !mostrarCalculadora"
                                    >
                                        <Calculator :size="14" />
                                        <span class="hidden sm:inline">Calcular</span>
                                    </button>
                                </div>
                                <InputError :message="form.errors.monto" />
                            </div>
                        </div>

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <CalculadoraGastos v-if="mostrarCalculadora" @usar="usarCalculadora" />
                        </Transition>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Categoría" />
                                <SelectInput v-model="form.categoria_id" class="mt-1.5">
                                    <option value="">Sin categoría</option>
                                    <option v-for="c in categoriasFiltradas" :key="c.id" :value="c.id">
                                        {{ c.nombre }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.categoria_id" />
                            </div>
                            <div>
                                <InputLabel value="Fecha" />
                                <TextInput v-model="form.fecha" type="date" :max="hoy" class="mt-1.5" />
                                <InputError :message="form.errors.fecha" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Descripción (opcional)" />
                            <TextInput
                                v-model="form.descripcion"
                                class="mt-1.5"
                                placeholder="Ej. Super, gasolina, pago de Netflix..."
                            />
                            <InputError :message="form.errors.descripcion" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-neutral-100 pt-6 sm:flex-row sm:justify-end">
                        <SecondaryButton
                            type="button"
                            class="!px-6"
                            @click="showModal = false"
                        >
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton
                            :disabled="form.processing"
                            class="!px-6"
                            :class="form.tipo === 'gasto' ? '!from-neutral-800 !via-neutral-900 !to-black' : ''"
                        >
                            <component :is="editando ? Pencil : Check" :size="14" />
                            {{ editando ? 'Guardar cambios' : 'Crear movimiento' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL transferencia -->
        <Modal :show="showTransferModal" max-width="2xl" @close="showTransferModal = false">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="relative overflow-hidden border-b border-neutral-200 bg-white px-8 py-6 text-neutral-900">

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200">
                            <ArrowLeftRight :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">Nueva transferencia</h2>
                            <p class="text-xs text-neutral-500">
                                Mueve dinero entre tus cuentas
                            </p>
                        </div>
                    </div>
                </div>

                <form class="p-8" @submit.prevent="guardarTransferencia">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-[1fr_auto_1fr]">
                            <div>
                                <InputLabel value="De la cuenta" />
                                <SelectInput v-model="transferForm.cuenta_origen_id" class="mt-1.5">
                                    <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">
                                        {{ c.nombre }} ({{ formatCurrency(c.saldo) }})
                                    </option>
                                </SelectInput>
                                <InputError :message="transferForm.errors.cuenta_origen_id" />

                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-1"
                                >
                                    <div
                                        v-if="cuentaOrigenT"
                                        class="mt-2.5 flex items-center gap-2 rounded-xl border px-3 py-2 shadow-sm"
                                        :style="{
                                            backgroundColor: hexToRgba(cuentaOrigenT.color, 0.08),
                                            borderColor: hexToRgba(cuentaOrigenT.color, 0.3),
                                        }"
                                    >
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(cuentaOrigenT.color, 0.18),
                                                color: cuentaOrigenT.color,
                                                '--tw-ring-color': hexToRgba(cuentaOrigenT.color, 0.35),
                                            }"
                                        >
                                            <Wallet :size="13" />
                                        </span>
                                        <div class="leading-tight">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                                Saldo actual
                                            </p>
                                            <p
                                                class="text-sm font-bold tabular-nums"
                                                :style="{ color: cuentaOrigenT.color }"
                                            >
                                                {{ formatCurrency(cuentaOrigenT.saldo) }}
                                            </p>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <div class="flex justify-center pb-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-neutral-200 bg-white text-neutral-500">
                                    <ArrowRight :size="18" />
                                </span>
                            </div>

                            <div>
                                <InputLabel value="A la cuenta" />
                                <SelectInput v-model="transferForm.cuenta_destino_id" class="mt-1.5">
                                    <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">
                                        {{ c.nombre }} ({{ formatCurrency(c.saldo) }})
                                    </option>
                                </SelectInput>
                                <InputError :message="transferForm.errors.cuenta_destino_id" />

                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 -translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-1"
                                >
                                    <div
                                        v-if="cuentaDestinoT"
                                        class="mt-2.5 flex items-center gap-2 rounded-xl border px-3 py-2 shadow-sm"
                                        :style="{
                                            backgroundColor: hexToRgba(cuentaDestinoT.color, 0.08),
                                            borderColor: hexToRgba(cuentaDestinoT.color, 0.3),
                                        }"
                                    >
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-inset"
                                            :style="{
                                                backgroundColor: hexToRgba(cuentaDestinoT.color, 0.18),
                                                color: cuentaDestinoT.color,
                                                '--tw-ring-color': hexToRgba(cuentaDestinoT.color, 0.35),
                                            }"
                                        >
                                            <Wallet :size="13" />
                                        </span>
                                        <div class="leading-tight">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                                Saldo actual
                                            </p>
                                            <p
                                                class="text-sm font-bold tabular-nums"
                                                :style="{ color: cuentaDestinoT.color }"
                                            >
                                                {{ formatCurrency(cuentaDestinoT.saldo) }}
                                            </p>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <div v-if="mismaCuenta" class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs font-semibold text-amber-800">
                            <X :size="14" class="mt-0.5 shrink-0" />
                            La cuenta de origen y destino no pueden ser la misma.
                        </div>
                        <div v-else-if="fondosInsuficientesT" class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-semibold text-rose-700">
                            <X :size="14" class="mt-0.5 shrink-0" />
                            La cuenta de origen no tiene saldo suficiente para esta transferencia.
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Monto a transferir" />
                                <CurrencyInput v-model="transferForm.monto" class="mt-1.5" />
                                <InputError :message="transferForm.errors.monto" />
                            </div>
                            <div>
                                <InputLabel value="Fecha" />
                                <TextInput v-model="transferForm.fecha" type="date" :max="hoy" class="mt-1.5" />
                                <InputError :message="transferForm.errors.fecha" />
                            </div>
                        </div>

                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                        >
                            <div
                                v-if="cuentaOrigenT && cuentaDestinoT && transferForm.monto && Number(transferForm.monto) > 0 && !mismaCuenta"
                                class="rounded-xl border border-purple-200 bg-white p-4"
                            >
                                <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    <Sparkles :size="13" />
                                    Vista previa de la transferencia
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <div class="rounded-lg bg-white p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Origen: {{ cuentaOrigenT.nombre }}
                                        </p>
                                        <p class="mt-1 text-xs text-neutral-500">
                                            Antes: <strong class="text-neutral-700">{{ formatCurrency(cuentaOrigenT.saldo) }}</strong>
                                        </p>
                                        <p class="text-xs text-rose-600">
                                            Despues: <strong>{{ formatCurrency(Number(cuentaOrigenT.saldo) - Number(transferForm.monto)) }}</strong>
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-white p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Destino: {{ cuentaDestinoT.nombre }}
                                        </p>
                                        <p class="mt-1 text-xs text-neutral-500">
                                            Antes: <strong class="text-neutral-700">{{ formatCurrency(cuentaDestinoT.saldo) }}</strong>
                                        </p>
                                        <p class="text-xs text-purple-700">
                                            Despues: <strong>{{ formatCurrency(Number(cuentaDestinoT.saldo) + Number(transferForm.monto)) }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <div>
                            <InputLabel value="Descripción (opcional)" />
                            <TextInput
                                v-model="transferForm.descripcion"
                                class="mt-1.5"
                                placeholder="Ej. Ahorro del mes, pago de tarjeta..."
                            />
                            <InputError :message="transferForm.errors.descripcion" />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-neutral-100 pt-6 sm:flex-row sm:justify-end">
                        <SecondaryButton
                            type="button"
                            class="!px-6"
                            @click="showTransferModal = false"
                        >
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton
                            :disabled="transferForm.processing || mismaCuenta"
                            class="!px-6"
                        >
                            <Check :size="14" />
                            Transferir
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>