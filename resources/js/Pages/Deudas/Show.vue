<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import {
    Plus, Trash2, ArrowLeft, CreditCard, CheckCircle2, TrendingDown,
    TrendingUp, Wallet, StickyNote, CalendarDays, CalendarClock, History,
    Landmark, Sparkles, Check, X, AlertTriangle,
} from '@lucide/vue';

const props = defineProps({
    deuda: Object,
    movimientos: Array,
    cuentas: Array,
});

const TIPOS = {
    abono: {
        label: 'Abono',
        accion: 'Registrar abono',
        icon: TrendingDown,
        color: '#9333ea',
        text: 'text-purple-700',
    },
    aumento: {
        label: 'Aumento',
        accion: 'Registrar aumento',
        icon: TrendingUp,
        color: '#d97706',
        text: 'text-amber-700',
    },
};

const showModal = ref(false);
const form = useForm({
    tipo: 'abono',
    cuenta_id: '',
    monto: '',
    fecha: hoyIso(),
    notas: '',
    descripcion: '',
});

function siguienteConsecutivo(tipo) {
    const count = props.movimientos.filter((m) => m.tipo === tipo).length;
    return count + 1;
}

function generarDescripcion(tipo, fecha) {
    const n = siguienteConsecutivo(tipo);
    const label = tipo === 'aumento' ? 'Aumento' : 'Pago';
    const fechaFmt = formatFecha(fecha);
    return `${label} ${n} - ${fechaFmt}`;
}

function abrirModal(tipo) {
    form.tipo = tipo;
    form.cuenta_id = '';
    form.fecha = hoyIso();
    form.monto = tipo === 'abono' && props.deuda.restante > 0 ? props.deuda.restante : '';
    form.notas = '';
    form.descripcion = generarDescripcion(tipo, form.fecha);
    form.clearErrors();
    showModal.value = true;
}

function onCambiarTipo(tipo) {
    form.tipo = tipo;
    form.descripcion = generarDescripcion(tipo, form.fecha);
}

function onCambiarFecha() {
    form.descripcion = generarDescripcion(form.tipo, form.fecha);
}

const esAumento = computed(() => form.tipo === 'aumento');
const cuentaSeleccionada = computed(() => props.cuentas.find((c) => c.id === form.cuenta_id));
const fondosInsuficientes = computed(
    () =>
        !esAumento.value &&
        cuentaSeleccionada.value &&
        Number(form.monto) > cuentaSeleccionada.value.saldo
);
const restanteProyectado = computed(() => {
    const monto = Number(form.monto) || 0;
    return esAumento.value ? props.deuda.restante + monto : props.deuda.restante - monto;
});

const porcentajeProyectado = computed(() => {
    const total = Number(props.deuda.monto_total_efectivo) || 1;
    const restante = Math.max(0, restanteProyectado.value);
    return Math.min(100, Math.round(((total - restante) / total) * 100));
});

function guardar() {
    form.post(route('abonos.store', props.deuda.id), {
        onSuccess: () => (showModal.value = false),
    });
}

function eliminarMovimiento(mov) {
    const esAumentoMov = mov.tipo === 'aumento';
    confirmar({
        title: esAumentoMov ? 'Eliminar aumento' : 'Eliminar abono',
        content: esAumentoMov
            ? 'Eliminar este aumento? Se revertira el monto en la cuenta asociada.'
            : 'Eliminar este abono? Se revertira el monto en la cuenta asociada.',
        onOk: () => router.delete(route('abonos.destroy', mov.id)),
    });
}
</script>

<template>
    <Head :title="deuda.nombre" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Volver -->
            <Link
                :href="route('deudas.index')"
                class="group mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-neutral-500 transition-all hover:text-purple-800"
            >
                <ArrowLeft :size="15" class="transition-transform" />
                Volver a deudas
            </Link>

            <!-- Card principal -->
            <Card :padded="false" class="overflow-hidden">
                <div
                    class="relative overflow-hidden p-6 lg:p-8"
                    :class="deuda.estado === 'pagada'
                        ? 'bg-white'
                        : 'bg-white'"
                >

                    <div class="relative flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                                :class="deuda.estado === 'pagada'
                                    ? 'bg-purple-100 text-purple-700 ring-1 ring-inset ring-purple-200'
                                    : 'bg-neutral-950 text-white ring-1 ring-inset ring-neutral-800'"
                            >
                                <component :is="deuda.estado === 'pagada' ? CheckCircle2 : CreditCard" :size="26" />
                            </div>
                            <div>
                                <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                                    {{ deuda.nombre }}
                                </h1>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-neutral-500">
                                    <span v-if="deuda.acreedor" class="inline-flex items-center gap-1">
                                        <Landmark :size="13" /> {{ deuda.acreedor }}
                                    </span>
                                    <span v-if="deuda.fecha_limite" class="inline-flex items-center gap-1">
                                        <CalendarClock :size="13" /> {{ formatFecha(deuda.fecha_limite) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                        :class="deuda.estado === 'pagada'
                                            ? 'border-purple-200 bg-purple-50 text-purple-800'
                                            : 'border-neutral-900 bg-neutral-950 text-white'"
                                    >
                                        {{ deuda.estado === 'pagada' ? 'Pagada' : 'Activa' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <SecondaryButton
                                class="!border-amber-200 !bg-amber-50 !text-amber-700 hover:!bg-amber-100"
                                @click="abrirModal('aumento')"
                            >
                                <TrendingUp :size="15" /> Pedir mas prestado
                            </SecondaryButton>
                            <PrimaryButton
                                @click="abrirModal('abono')"
                            >
                                <Plus :size="16" /> Registrar abono
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="relative mt-7">
                        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-neutral-500">
                            <span>Progreso de pago</span>
                            <span class="text-sm font-bold text-purple-800">{{ deuda.progreso }}%</span>
                        </div>
                        <ProgressBar
                            :value="deuda.progreso"
                            :color="deuda.estado === 'pagada' ? '#9333ea' : '#e11d48'"
                        />
                        <p
                            v-if="deuda.aumentos > 0"
                            class="mt-3 inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700"
                        >
                            <TrendingUp :size="12" />
                            Monto original {{ formatCurrency(deuda.monto_total) }} +
                            {{ formatCurrency(deuda.aumentos) }} en aumentos
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                    <div class="rounded-xl border border-neutral-200 bg-white p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            <Wallet :size="13" /> Total
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900">
                            {{ formatCurrency(deuda.monto_total_efectivo) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-purple-200 bg-white p-4">
                        <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-neutral-500">
                            <TrendingDown :size="13" /> Pagado
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-purple-800">
                            {{ formatCurrency(deuda.pagado) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4 text-white">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-purple-300">
                            <CreditCard :size="13" /> Restante
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-white">
                            {{ formatCurrency(deuda.restante) }}
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Historial -->
            <Card class="mt-6 overflow-hidden" :padded="false">
                <div class="border-b border-neutral-100 bg-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                                <History :size="16" />
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-neutral-900">Historial de movimientos</h3>
                                <p class="text-xs text-neutral-500">
                                    {{ movimientos.length }} movimiento(s)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="movimientos.length === 0" class="flex flex-col items-center gap-2 px-5 py-16 text-center text-sm text-neutral-400">
                    <History :size="32" class="text-neutral-200" />
                    Aun no has registrado abonos ni aumentos.
                </div>

                <div v-else class="divide-y divide-neutral-100">
                    <div
                        v-for="(m, i) in movimientos"
                        :key="m.id"
                        class="group relative flex animate-fade-in items-center gap-4 px-6 py-4 transition-all duration-200 hover:bg-purple-50/50"
                        :style="{ animationDelay: `${i * 30}ms` }"
                    >
                        <!-- Barra lateral tipo -->
                        <span
                            class="absolute inset-y-2 left-0 w-1 rounded-r-full opacity-60 transition-all duration-300 group-hover:inset-y-0 group-hover:opacity-100"
                            :class="m.tipo === 'aumento' ? 'bg-amber-500' : 'bg-purple-600'"
                        ></span>

                        <!-- Icono -->
                        <div
                            class="relative ml-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                            :class="m.tipo === 'aumento'
                                ? 'bg-white text-amber-700'
                                : 'bg-white text-purple-700'"
                        >
                            <component :is="m.tipo === 'aumento' ? TrendingUp : TrendingDown" :size="18"stroke-width="2.2" />
                            <span
                                class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset"
                                :class="m.tipo === 'aumento' ? 'ring-amber-200' : 'ring-purple-200'"
                            ></span>
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-neutral-900">
                                    {{ m.descripcion || (m.tipo === 'aumento' ? 'Aumento' : 'Abono') }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                    :class="m.tipo === 'aumento'
                                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                                        : 'border-purple-200 bg-purple-50 text-purple-800'"
                                >
                                    {{ m.tipo === 'aumento' ? 'Aumento' : 'Abono' }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-neutral-500">
                                <span class="inline-flex items-center gap-1">
                                    <CalendarDays :size="11" /> {{ formatFecha(m.fecha) }}
                                </span>
                                <span v-if="m.cuenta" class="inline-flex items-center gap-1">
                                    <Wallet :size="11" /> {{ m.cuenta.nombre }}
                                </span>
                                <span v-if="m.notas" class="inline-flex items-center gap-1">
                                    <StickyNote :size="11" /> {{ m.notas }}
                                </span>
                            </div>
                        </div>

                        <!-- Monto -->
                        <div
                            class="shrink-0 text-right text-base font-bold tabular-nums"
                            :class="m.tipo === 'aumento' ? 'text-amber-600' : 'text-purple-800'"
                        >
                            {{ m.tipo === 'aumento' ? '+' : '-' }} {{ formatCurrency(m.monto) }}
                        </div>

                        <!-- Boton eliminar mejorado -->
                        <div class="relative shrink-0">
                            <button
                                class="peer flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-neutral-300 transition-all duration-300 hover:border-rose-300 hover:bg-neutral-50 hover:text-rose-600 hover:shadow-md group-hover:text-neutral-400 group-hover:border-neutral-200"
                                title="Eliminar movimiento"
                                @click="eliminarMovimiento(m)"
                            >
                                <Trash2 :size="16" class="transition-transform duration-300" />
                            </button>
                            <!-- Tooltip -->
                            <span
                                class="pointer-events-none absolute -top-9 right-0 whitespace-nowrap rounded-lg border border-neutral-800 bg-neutral-900 px-2.5 py-1 text-[10px] font-semibold text-white opacity-0 shadow-lg transition-all duration-200 peer-hover:-top-8 peer-hover:opacity-100"
                            >
                                Eliminar
                                <span class="absolute -bottom-1 right-3 h-2 w-2 rotate-45 border-r border-b border-neutral-800 bg-neutral-900"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <!-- MODAL 2xl mejorado -->
        <Modal :show="showModal" max-width="2xl" @close="showModal = false">
            <div class="overflow-hidden rounded-xl bg-white">

                <!-- Header -->
                <div
                    class="relative overflow-hidden border-b px-8 py-6 text-neutral-900"
                    :class="esAumento
                        ? 'border-neutral-200 bg-white'
                        : 'border-neutral-200 bg-white'"
                >

                    <div class="relative flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl transition-colors duration-300"
                                :class="esAumento
                                    ? 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200'
                                    : 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200'"
                            >
                                <component :is="TIPOS[form.tipo].icon" :size="20" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold tracking-tight">
                                    {{ TIPOS[form.tipo].accion }}
                                </h2>
                                <p class="text-xs text-neutral-500">
                                    {{ esAumento ? 'Aumenta el monto total de la deuda' : 'Registra un abono a esta deuda' }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-neutral-400 transition-all hover:bg-neutral-100 hover:text-neutral-700"
                            @click="showModal = false"
                        >
                            <X :size="18" />
                        </button>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <!-- Switch tipo grande -->
                        <div>
                            <InputLabel value="Tipo de movimiento" />
                            <div class="mt-2 inline-flex w-full rounded-xl border border-neutral-200 bg-neutral-50 p-1.5">
                                <button
                                    v-for="(cfg, clave) in TIPOS"
                                    :key="clave"
                                    type="button"
                                    class="relative flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-bold transition-all duration-300"
                                    :class="form.tipo === clave
                                        ? clave === 'aumento'
                                            ? 'bg-amber-500 text-white shadow-sm'
                                            : 'bg-gradient-to-br from-purple-600 to-purple-800 text-white shadow-md shadow-purple-900/30'
                                        : 'text-neutral-500 hover:bg-white hover:text-neutral-800'"
                                    @click="onCambiarTipo(clave)"
                                >
                                    <component :is="cfg.icon" :size="16" />
                                    {{ cfg.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Cuenta -->
                        <div>
                            <InputLabel :value="esAumento ? 'Depositar en (opcional)' : 'Pagar desde'" />
                            <SelectInput v-model="form.cuenta_id" class="mt-1.5">
                                <option value="">
                                    {{ esAumento ? '(No depositar en ninguna cuenta)' : '(No descontar de ninguna cuenta)' }}
                                </option>
                                <option v-for="c in cuentas" :key="c.id" :value="c.id">
                                    {{ c.nombre }} ({{ formatCurrency(c.saldo) }})
                                </option>
                            </SelectInput>
                            <p v-if="fondosInsuficientes" class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-rose-600">
                                <AlertTriangle :size="12" />
                                Esa cuenta no tiene saldo suficiente para este abono.
                            </p>
                            <InputError :message="form.errors.cuenta_id" />
                        </div>

                        <!-- Monto + Fecha -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel
                                    :value="esAumento
                                        ? `Monto del aumento (deuda quedaria en ${formatCurrency(restanteProyectado)})`
                                        : `Monto del abono (restan ${formatCurrency(deuda.restante)})`"
                                />
                                <CurrencyInput v-model="form.monto" class="mt-1.5" />
                                <InputError :message="form.errors.monto" />
                            </div>
                            <div>
                                <InputLabel value="Fecha" />
                                <TextInput
                                    v-model="form.fecha"
                                    type="date"
                                    :max="hoyIso()"
                                    class="mt-1.5"
                                    @change="onCambiarFecha"
                                />
                                <InputError :message="form.errors.fecha" />
                            </div>
                        </div>

                        <!-- Preview proyectado (solo si hay monto) -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 -translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-1"
                        >
                            <div
                                v-if="form.monto && Number(form.monto) > 0"
                                class="rounded-xl border p-4 transition-colors"
                                :class="esAumento
                                    ? 'border-amber-200 bg-white'
                                    : 'border-purple-200 bg-white'"
                            >
                                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider"
                                    :class="esAumento ? 'text-amber-700' : 'text-purple-700'"
                                >
                                    <Sparkles :size="13" />
                                    Vista previa del movimiento
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Antes
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums text-neutral-700">
                                            {{ formatCurrency(deuda.restante) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider"
                                            :class="esAumento ? 'text-amber-700' : 'text-purple-700'"
                                        >
                                            {{ esAumento ? '+' : '-' }} Monto
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums"
                                            :class="esAumento ? 'text-amber-700' : 'text-purple-700'"
                                        >
                                            {{ formatCurrency(form.monto) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Despues
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums text-neutral-900">
                                            {{ formatCurrency(Math.max(0, restanteProyectado)) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <ProgressBar
                                        :value="porcentajeProyectado"
                                        :color="esAumento ? '#d97706' : '#9333ea'"
                                    />
                                </div>
                            </div>
                        </Transition>

                        <!-- Descripcion automatica -->
                        <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-600/15 text-purple-700">
                                    <Sparkles :size="16" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <InputLabel value="Descripcion (automatica)" />
                                    <TextInput
                                        v-model="form.descripcion"
                                        class="mt-1.5"
                                        placeholder="Se genera automaticamente"
                                    />
                                    <p class="mt-1.5 text-xs text-neutral-500">
                                        Puedes editarla si lo necesitas. El consecutivo se calcula segun el historial.
                                    </p>
                                    <InputError :message="form.errors.descripcion" />
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <InputLabel value="Notas (opcional)" />
                            <TextInput
                                v-model="form.notas"
                                class="mt-1.5"
                                :placeholder="esAumento ? 'Ej. Prestamo adicional del banco...' : 'Ej. Pago parcial acordado...'"
                            />
                            <InputError :message="form.errors.notas" />
                        </div>
                    </div>

                    <!-- Footer -->
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
                            :class="esAumento
                                ? '!bg-amber-500 hover:!bg-amber-400'
                                : '!bg-purple-700 hover:!bg-purple-600'"
                        >
                            <Check :size="14" />
                            {{ esAumento ? 'Registrar aumento' : 'Registrar abono' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>