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
    Plus, Trash2, ArrowLeft, ArrowUpCircle, Wallet, StickyNote,
    CalendarDays, CalendarClock, History, Briefcase, User, Clock,
    CheckCircle2, XCircle, Sparkles, Check,
} from '@lucide/vue';

const props = defineProps({
    proyecto: Object,
    pagos: Array,
    estados: Object,
    cuentas: Array,
});

const ESTADO_COLOR = {
    activo: '#9333ea',
    terminado: '#16a34a',
    cancelado: '#94a3b8',
};
const ESTADO_ICON = {
    activo: Clock,
    terminado: CheckCircle2,
    cancelado: XCircle,
};
const ESTADO_GRADIENTE = {
    activo: 'bg-white',
    terminado: 'bg-white',
    cancelado: 'bg-white',
};

const color = computed(() => ESTADO_COLOR[props.proyecto.estado]);

const showModal = ref(false);
const form = useForm({
    cuenta_id: '',
    monto: '',
    fecha: hoyIso(),
    notas: '',
    descripcion: '',
});

// Descripcion consecutiva automatica
function siguienteConsecutivo() {
    return props.pagos.length + 1;
}

function generarDescripcion(fecha) {
    const n = siguienteConsecutivo();
    const fechaFmt = formatFecha(fecha);
    return `Pago ${n} - ${fechaFmt}`;
}

function abrirPago() {
    form.cuenta_id = '';
    form.fecha = hoyIso();
    form.monto = props.proyecto.pendiente > 0 ? props.proyecto.pendiente : '';
    form.notas = '';
    form.descripcion = generarDescripcion(form.fecha);
    form.clearErrors();
    showModal.value = true;
}

function onCambiarFecha() {
    form.descripcion = generarDescripcion(form.fecha);
}

const cuentaSeleccionada = computed(() => props.cuentas.find((c) => c.id === form.cuenta_id));
const pendienteProyectado = computed(() => {
    const monto = Number(form.monto) || 0;
    return Math.max(0, props.proyecto.pendiente - monto);
});
const porcentajeProyectado = computed(() => {
    const total = Number(props.proyecto.monto_cobrado) || 1;
    const pendiente = pendienteProyectado.value;
    return Math.min(100, Math.round(((total - pendiente) / total) * 100));
});
const pagoCompleto = computed(() => {
    const monto = Number(form.monto) || 0;
    return monto > 0 && monto >= props.proyecto.pendiente;
});

function guardar() {
    form.post(route('pagos.store', props.proyecto.id), {
        onSuccess: () => (showModal.value = false),
    });
}

function eliminarPago(pago) {
    confirmar({
        title: 'Eliminar pago',
        content: 'Se revertirá el monto en la cuenta asociada.',
        onOk: () => router.delete(route('pagos.destroy', pago.id)),
    });
}
</script>

<template>
    <Head :title="proyecto.nombre" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Volver -->
            <Link
                :href="route('proyectos.index')"
                class="group mb-5 inline-flex items-center gap-1.5 text-sm font-semibold text-neutral-500 transition-all hover:text-purple-800"
            >
                <ArrowLeft :size="15" class="transition-transform" />
                Volver a proyectos
            </Link>

            <!-- Card principal -->
            <Card :padded="false" class="overflow-hidden">
                <div
                    class="relative overflow-hidden p-6 lg:p-8"
                    :class="ESTADO_GRADIENTE[proyecto.estado]"
                >
                    <div
                        class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full opacity-30 blur-3xl"
                        :style="{ backgroundColor: color }"
                    ></div>

                    <div class="relative flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                                :style="{
                                    background: `linear-gradient(135deg, ${color}, ${color}cc)`,
                                    color: 'white',
                                }"
                            >
                                <Briefcase :size="26" />
                            </div>
                            <div>
                                <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                                    {{ proyecto.nombre }}
                                </h1>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-neutral-500">
                                    <span v-if="proyecto.cliente" class="inline-flex items-center gap-1">
                                        <User :size="13" /> {{ proyecto.cliente }}
                                    </span>
                                    <span v-if="proyecto.fecha_entrega" class="inline-flex items-center gap-1">
                                        <CalendarClock :size="13" /> {{ formatFecha(proyecto.fecha_entrega) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                        :style="{
                                            backgroundColor: color + '14',
                                            color: color,
                                            borderColor: color + '40',
                                        }"
                                    >
                                        <component :is="ESTADO_ICON[proyecto.estado]" :size="11" />
                                        {{ estados[proyecto.estado] }}
                                    </span>
                                </div>
                                <p v-if="proyecto.descripcion" class="mt-2 max-w-2xl text-sm text-neutral-600">
                                    {{ proyecto.descripcion }}
                                </p>
                            </div>
                        </div>

                        <PrimaryButton
                            @click="abrirPago"
                        >
                            <Plus :size="16" /> Registrar pago
                        </PrimaryButton>
                    </div>

                    <div class="relative mt-7">
                        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-neutral-500">
                            <span>Progreso de cobro</span>
                            <span class="text-sm font-bold" :style="{ color }">{{ proyecto.progreso }}%</span>
                        </div>
                        <ProgressBar :value="proyecto.progreso" :color="color" />
                    </div>
                </div>

                <!-- Resumen 3 columnas -->
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                    <div class="rounded-xl border border-neutral-200 bg-white p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            <Briefcase :size="13" /> Cobrado
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-neutral-900">
                            {{ formatCurrency(proyecto.monto_cobrado) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-purple-200 bg-white p-4">
                        <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-neutral-500">
                            <ArrowUpCircle :size="13" /> Pagado
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-purple-800">
                            {{ formatCurrency(proyecto.pagado) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-neutral-800 bg-neutral-950 p-4 text-white">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-purple-300">
                            <Clock :size="13" /> Pendiente
                        </div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-white">
                            {{ formatCurrency(proyecto.pendiente) }}
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
                                <h3 class="text-sm font-semibold text-neutral-900">Historial de pagos</h3>
                                <p class="text-xs text-neutral-500">
                                    {{ pagos.length }} pago(s)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="pagos.length === 0" class="flex flex-col items-center gap-2 px-5 py-16 text-center text-sm text-neutral-400">
                    <History :size="32" class="text-neutral-200" />
                    Aun no has registrado pagos.
                </div>

                <div v-else class="divide-y divide-neutral-100">
                    <div
                        v-for="(p, i) in pagos"
                        :key="p.id"
                        class="group relative flex animate-fade-in items-center gap-4 px-6 py-4 transition-all duration-200 hover:bg-purple-50/50"
                        :style="{ animationDelay: `${i * 30}ms` }"
                    >
                        <!-- Barra lateral -->
                        <span
                            class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-purple-600 opacity-60 transition-all duration-300 group-hover:inset-y-0 group-hover:opacity-100"
                        ></span>

                        <!-- Icono -->
                        <div class="relative ml-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-purple-700 shadow-sm transition-transform duration-300">
                            <ArrowUpCircle :size="18" stroke-width="2.2" />
                            <span class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset ring-purple-200"></span>
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-neutral-900">
                                    {{ p.descripcion || 'Pago recibido' }}
                                </span>
                                <span class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-800">
                                    Pago
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-neutral-500">
                                <span class="inline-flex items-center gap-1">
                                    <CalendarDays :size="11" /> {{ formatFecha(p.fecha) }}
                                </span>
                                <span v-if="p.cuenta" class="inline-flex items-center gap-1">
                                    <Wallet :size="11" /> {{ p.cuenta.nombre }}
                                </span>
                                <span v-if="p.notas" class="inline-flex items-center gap-1">
                                    <StickyNote :size="11" /> {{ p.notas }}
                                </span>
                            </div>
                        </div>

                        <!-- Monto -->
                        <div class="shrink-0 text-right text-base font-bold tabular-nums text-purple-800">
                            + {{ formatCurrency(p.monto) }}
                        </div>

                        <!-- Boton eliminar -->
                        <div class="relative shrink-0">
                            <button
                                class="peer flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-neutral-300 transition-all duration-300 hover:border-rose-300 hover:bg-neutral-50 hover:text-rose-600 hover:shadow-md group-hover:border-neutral-200 group-hover:text-neutral-400"
                                title="Eliminar pago"
                                @click="eliminarPago(p)"
                            >
                                <Trash2 :size="16" class="transition-transform duration-300" />
                            </button>
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

        <!-- MODAL 2xl -->
        <Modal :show="showModal" max-width="2xl" @close="showModal = false">
            <div class="overflow-hidden rounded-xl bg-white">

                <!-- Header -->
                <div class="relative overflow-hidden border-b border-neutral-200 bg-white px-8 py-6 text-neutral-900">

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200">
                            <ArrowUpCircle :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">Registrar pago recibido</h2>
                            <p class="text-xs text-neutral-500">
                                Registra un pago de este proyecto y actualiza el saldo pendiente
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <!-- Cuenta -->
                        <div>
                            <InputLabel value="Depositar en" />
                            <SelectInput v-model="form.cuenta_id" class="mt-1.5">
                                <option value="">(No depositar en ninguna cuenta)</option>
                                <option v-for="c in cuentas" :key="c.id" :value="c.id">
                                    {{ c.nombre }} ({{ formatCurrency(c.saldo) }})
                                </option>
                            </SelectInput>
                            <InputError :message="form.errors.cuenta_id" />
                        </div>

                        <!-- Monto + Fecha -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel :value="`Monto recibido (faltan ${formatCurrency(proyecto.pendiente)})`" />
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

                        <!-- Preview proyectado -->
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
                                :class="pagoCompleto
                                    ? 'border-green-200 bg-white'
                                    : 'border-purple-200 bg-white'"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider"
                                    :class="pagoCompleto ? 'text-green-700' : 'text-purple-700'"
                                >
                                    <Sparkles :size="13" />
                                    {{ pagoCompleto ? 'Pago completo' : 'Vista previa del pago' }}
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Antes
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums text-neutral-700">
                                            {{ formatCurrency(proyecto.pendiente) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider"
                                            :class="pagoCompleto ? 'text-green-700' : 'text-purple-700'"
                                        >
                                            - Monto
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums"
                                            :class="pagoCompleto ? 'text-green-700' : 'text-purple-700'"
                                        >
                                            {{ formatCurrency(form.monto) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-neutral-500">
                                            Despues
                                        </p>
                                        <p class="mt-1 text-sm font-bold tabular-nums text-neutral-900">
                                            {{ formatCurrency(pendienteProyectado) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <ProgressBar
                                        :value="porcentajeProyectado"
                                        :color="pagoCompleto ? '#16a34a' : '#9333ea'"
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
                                placeholder="Ej. Anticipo, pago final..."
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
                            :class="pagoCompleto
                                ? '!bg-green-600 hover:!bg-green-500'
                                : '!bg-purple-700 hover:!bg-purple-600'"
                        >
                            <Check :size="14" />
                            {{ pagoCompleto ? 'Registrar pago final' : 'Registrar pago' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>