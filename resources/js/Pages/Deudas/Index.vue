<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import {
    Plus, Pencil, Trash2, Eye, CreditCard, CalendarClock, CheckCircle2,
    Landmark, Coins, CalendarDays, StickyNote, TrendingUp, Sparkles, Check,
} from '@lucide/vue';

const props = defineProps({
    deudas: Array,
});

const showModal = ref(false);
const editando = ref(null);
const filtro = ref('activas'); // 'activas' | 'pagadas'

const form = useForm({
    nombre: '',
    acreedor: '',
    monto_total: '',
    fecha_inicio: hoyIso(),
    fecha_limite: '',
    notas: '',
});

// Listas filtradas
const deudasActivasLista = computed(() =>
    props.deudas.filter((d) => d.estado !== 'pagada')
);
const deudasPagadasLista = computed(() =>
    props.deudas.filter((d) => d.estado === 'pagada')
);

const deudasFiltradas = computed(() =>
    filtro.value === 'activas' ? deudasActivasLista.value : deudasPagadasLista.value
);

const totalPendiente = computed(() =>
    deudasActivasLista.value.reduce((s, d) => s + Number(d.restante || 0), 0)
);
const totalPagado = computed(() =>
    deudasPagadasLista.value.reduce((s, d) => s + Number(d.pagado || 0), 0)
);

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.acreedor = '';
    form.monto_total = '';
    form.fecha_inicio = hoyIso();
    form.fecha_limite = '';
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(deuda) {
    editando.value = deuda;
    form.nombre = deuda.nombre;
    form.acreedor = deuda.acreedor || '';
    form.monto_total = deuda.monto_total;
    form.fecha_inicio = deuda.fecha_inicio ? deuda.fecha_inicio.slice(0, 10) : '';
    form.fecha_limite = deuda.fecha_limite ? deuda.fecha_limite.slice(0, 10) : '';
    form.notas = deuda.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('deudas.update', editando.value.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('deudas.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function eliminar(deuda) {
    confirmar({
        title: 'Eliminar deuda',
        content: `Eliminar "${deuda.nombre}" y todo su historial de abonos?`,
        onOk: () => router.delete(route('deudas.destroy', deuda.id)),
    });
}
</script>

<template>
    <Head title="Deudas" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <CreditCard :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Deudas
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Lo que debes y cuanto llevas pagado
                        </p>
                    </div>
                </div>

                <PrimaryButton
                    @click="abrirCrear"
                >
                    <Plus :size="16" /> Nueva deuda
                </PrimaryButton>
            </div>

            <!-- Switch Activas / Pagadas -->
            <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="inline-flex w-full items-center gap-1 rounded-xl border border-neutral-200 bg-white p-1 shadow-sm sm:w-auto">
                    <button
                        class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 sm:flex-initial"
                        :class="filtro === 'activas'
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                        @click="filtro = 'activas'"
                    >
                        <CreditCard :size="14" />
                        Activas
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :class="filtro === 'activas'
                                ? 'bg-white/25 text-white'
                                : 'bg-purple-100 text-purple-800'"
                        >
                            {{ deudasActivasLista.length }}
                        </span>
                    </button>

                    <button
                        class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 sm:flex-initial"
                        :class="filtro === 'pagadas'
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                        @click="filtro = 'pagadas'"
                    >
                        <CheckCircle2 :size="14" />
                        Pagadas
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :class="filtro === 'pagadas'
                                ? 'bg-white/25 text-white'
                                : 'bg-purple-100 text-purple-800'"
                        >
                            {{ deudasPagadasLista.length }}
                        </span>
                    </button>
                </div>

                <!-- Chip de total segun filtro -->
                <span
                    class="rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="filtro === 'activas'
                        ? 'border-neutral-900 bg-neutral-950 text-white'
                        : 'border-purple-200 bg-purple-50 text-purple-800'"
                >
                    {{ filtro === 'activas' ? 'Pendiente' : 'Total pagado' }}:
                    {{ filtro === 'activas' ? formatCurrency(totalPendiente) : formatCurrency(totalPagado) }}
                </span>
            </div>

            <!-- Empty state general (sin deudas) -->
            <div
                v-if="deudas.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <CreditCard :size="24" />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    No tienes deudas registradas
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    Que se mantenga asi, o agrega una para llevar el control.
                </p>
            </div>

            <!-- Empty state del filtro (hay deudas pero ninguna en ese filtro) -->
            <div
                v-else-if="deudasFiltradas.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <component :is="filtro === 'activas' ? CreditCard : CheckCircle2" :size="24" />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    {{ filtro === 'activas' ? 'No tienes deudas activas' : 'Aun no has pagado ninguna deuda' }}
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    {{ filtro === 'activas'
                        ? 'Todas tus deudas estan pagadas.'
                        : 'Cuando pagues una deuda aparecera aqui.' }}
                </p>
            </div>

            <!-- Grid de cards -->
            <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="(deuda, i) in deudasFiltradas"
                    :key="deuda.id"
                    class="card-lift group animate-fade-in relative overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm"
                    :style="{ animationDelay: `${i * 40}ms` }"
                >
                    <!-- Barra superior segun estado -->
                    <div
                        class="h-1.5 w-full"
                        :class="deuda.estado === 'pagada'
                            ? 'bg-purple-500'
                            : 'bg-gradient-to-r from-neutral-950 via-purple-800 to-neutral-950'"
                    ></div>

                    <div class="p-5">
                        <!-- Icono + nombre + estado -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                                    :class="deuda.estado === 'pagada'
                                        ? 'bg-white text-purple-700'
                                        : 'bg-white text-neutral-950'"
                                >
                                    <component :is="deuda.estado === 'pagada' ? CheckCircle2 : CreditCard" :size="18"stroke-width="2.2" />
                                    <span
                                        class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset"
                                        :class="deuda.estado === 'pagada' ? 'ring-purple-200' : 'ring-neutral-300'"
                                    ></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-neutral-900">{{ deuda.nombre }}</p>
                                    <p v-if="deuda.acreedor" class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500">
                                        <Landmark :size="11" /> {{ deuda.acreedor }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="shrink-0 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :class="deuda.estado === 'pagada'
                                    ? 'border-purple-200 bg-purple-50 text-purple-800'
                                    : 'border-neutral-900 bg-neutral-950 text-white'"
                            >
                                {{ deuda.estado === 'pagada' ? 'Pagada' : 'Activa' }}
                            </span>
                        </div>

                        <!-- Restante -->
                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                                Restante
                            </span>
                            <p
                                class="text-2xl font-bold tracking-tight tabular-nums"
                                :class="deuda.estado === 'pagada' ? 'text-purple-700' : 'text-neutral-950'"
                            >
                                {{ formatCurrency(deuda.restante) }}
                            </p>
                        </div>

                        <!-- Progress -->
                        <div class="mt-3 flex items-center gap-3">
                            <ProgressBar
                                :value="deuda.progreso"
                                class="flex-1"
                                :color="deuda.estado === 'pagada' ? '#9333ea' : '#e11d48'"
                            />
                            <span class="w-10 shrink-0 text-right text-xs font-bold text-neutral-700">
                                {{ deuda.progreso }}%
                            </span>
                        </div>

                        <!-- Info secundaria -->
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-1 text-[11px] text-neutral-500">
                            <span>
                                Pagado {{ formatCurrency(deuda.pagado) }} de
                                {{ formatCurrency(deuda.monto_total_efectivo) }}
                            </span>
                            <span v-if="deuda.fecha_limite" class="inline-flex items-center gap-1">
                                <CalendarClock :size="11" /> {{ formatFecha(deuda.fecha_limite) }}
                            </span>
                        </div>

                        <div
                            v-if="deuda.aumentos > 0"
                            class="mt-2 inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700"
                        >
                            <TrendingUp :size="10" /> +{{ formatCurrency(deuda.aumentos) }} en aumentos
                        </div>

                        <!-- Acciones -->
                        <div class="mt-4 flex items-center gap-2 border-t border-neutral-100 pt-4">
                            <Link
                                :href="route('deudas.show', deuda.id)"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 shine rounded-xl bg-neutral-950 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-purple-900/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-purple-800 hover:shadow-lg hover:shadow-purple-700/30 active:scale-95"
                            >
                                <Eye :size="13" /> Ver / abonar
                            </Link>
                            <button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-800 transition-all duration-200 hover:border-purple-300 hover:bg-purple-100 hover:shadow-sm"
                                title="Editar"
                                @click="abrirEditar(deuda)"
                            >
                                <Pencil :size="14" />
                            </button>
                            <button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 hover:shadow-sm"
                                title="Eliminar"
                                @click="eliminar(deuda)"
                            >
                                <Trash2 :size="14" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2xl -->
        <Modal :show="showModal" max-width="2xl" @close="showModal = false">
            <div class="overflow-hidden rounded-xl bg-white">

                <!-- Header -->
                <div class="relative overflow-hidden border-b border-neutral-200 bg-white px-8 py-6 text-neutral-900">

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200">
                            <component :is="editando ? Pencil : Sparkles" :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">
                                {{ editando ? 'Editar deuda' : 'Nueva deuda' }}
                            </h2>
                            <p class="text-xs text-neutral-500">
                                {{ editando ? 'Actualiza los datos de la deuda' : 'Registra una nueva deuda para llevar su control' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <!-- Nombre -->
                        <div>
                            <InputLabel value="Nombre de la deuda" />
                            <TextInput
                                v-model="form.nombre"
                                class="mt-1.5"
                                placeholder="Ej. Tarjeta Santander, Prestamo personal..."
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <!-- Acreedor + Monto total -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Acreedor (opcional)" />
                                <TextInput
                                    v-model="form.acreedor"
                                    class="mt-1.5"
                                    placeholder="A quien le debes?"
                                />
                                <InputError :message="form.errors.acreedor" />
                            </div>
                            <div>
                                <InputLabel value="Monto total" />
                                <CurrencyInput v-model="form.monto_total" class="mt-1.5" />
                                <InputError :message="form.errors.monto_total" />
                            </div>
                        </div>

                        <!-- Fecha inicio + Fecha limite -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Fecha de inicio" />
                                <TextInput v-model="form.fecha_inicio" type="date" class="mt-1.5" />
                                <InputError :message="form.errors.fecha_inicio" />
                            </div>
                            <div>
                                <InputLabel value="Fecha limite (opcional)" />
                                <TextInput v-model="form.fecha_limite" type="date" class="mt-1.5" />
                                <InputError :message="form.errors.fecha_limite" />
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <InputLabel value="Notas (opcional)" />
                            <Textarea
                                v-model="form.notas"
                                class="mt-1.5"
                                :rows="3"
                                placeholder="Ej. Tasa de interes, condiciones, recordatorios..."
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
                        >
                            <component :is="editando ? Pencil : Check" :size="14" />
                            {{ editando ? 'Guardar cambios' : 'Crear deuda' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>