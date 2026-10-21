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
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import {
    Plus, Pencil, Trash2, Eye, Briefcase, User, CalendarClock, CalendarDays,
    Coins, StickyNote, Clock, CheckCircle2, XCircle, Sparkles, Check,
} from '@lucide/vue';

const props = defineProps({
    proyectos: Array,
    estados: Object,
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

const showModal = ref(false);
const editando = ref(null);
const filtro = ref('activo'); // 'activo' | 'terminado' | 'cancelado'

const form = useForm({
    nombre: '',
    cliente: '',
    descripcion: '',
    monto_cobrado: '',
    fecha_inicio: hoyIso(),
    fecha_entrega: '',
    estado: 'activo',
    notas: '',
});

// Listas filtradas
const proyectosActivosLista = computed(() => props.proyectos.filter((p) => p.estado === 'activo'));
const proyectosTerminadosLista = computed(() => props.proyectos.filter((p) => p.estado === 'terminado'));
const proyectosCanceladosLista = computed(() => props.proyectos.filter((p) => p.estado === 'cancelado'));

const proyectosFiltrados = computed(() => {
    if (filtro.value === 'terminado') return proyectosTerminadosLista.value;
    if (filtro.value === 'cancelado') return proyectosCanceladosLista.value;
    return proyectosActivosLista.value;
});

// Totales segun filtro
const totalPorCobrar = computed(() =>
    proyectosActivosLista.value.reduce((s, p) => s + Number(p.pendiente || 0), 0)
);
const totalCobradoTerminados = computed(() =>
    proyectosTerminadosLista.value.reduce((s, p) => s + Number(p.pagado || 0), 0)
);
const totalCancelados = computed(() =>
    proyectosCanceladosLista.value.reduce((s, p) => s + Number(p.pagado || 0), 0)
);

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.cliente = '';
    form.descripcion = '';
    form.monto_cobrado = '';
    form.fecha_inicio = hoyIso();
    form.fecha_entrega = '';
    form.estado = 'activo';
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(proyecto) {
    editando.value = proyecto;
    form.nombre = proyecto.nombre;
    form.cliente = proyecto.cliente || '';
    form.descripcion = proyecto.descripcion || '';
    form.monto_cobrado = proyecto.monto_cobrado;
    form.fecha_inicio = proyecto.fecha_inicio ? proyecto.fecha_inicio.slice(0, 10) : '';
    form.fecha_entrega = proyecto.fecha_entrega ? proyecto.fecha_entrega.slice(0, 10) : '';
    form.estado = proyecto.estado;
    form.notas = proyecto.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('proyectos.update', editando.value.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('proyectos.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function eliminar(proyecto) {
    confirmar({
        title: 'Eliminar proyecto',
        content: `Eliminar "${proyecto.nombre}" y todo su historial de pagos?`,
        onOk: () => router.delete(route('proyectos.destroy', proyecto.id)),
    });
}
</script>

<template>
    <Head title="Proyectos" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <Briefcase :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Proyectos
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Tus trabajos freelance: que cobras y que te han pagado
                        </p>
                    </div>
                </div>

                <PrimaryButton
                    @click="abrirCrear"
                >
                    <Plus :size="16" /> Nuevo proyecto
                </PrimaryButton>
            </div>

            <!-- Switch de filtros -->
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex w-full items-center gap-1 rounded-xl border border-neutral-200 bg-white p-1 shadow-sm lg:w-auto">
                    <!-- Activos -->
                    <button
                        class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 lg:flex-initial"
                        :class="filtro === 'activo'
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                        @click="filtro = 'activo'"
                    >
                        <Clock :size="14" />
                        Activos
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :class="filtro === 'activo' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                        >
                            {{ proyectosActivosLista.length }}
                        </span>
                    </button>

                    <!-- Terminados -->
                    <button
                        class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 lg:flex-initial"
                        :class="filtro === 'terminado'
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                        @click="filtro = 'terminado'"
                    >
                        <CheckCircle2 :size="14" />
                        Terminados
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :class="filtro === 'terminado' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                        >
                            {{ proyectosTerminadosLista.length }}
                        </span>
                    </button>

                    <!-- Cancelados -->
                    <button
                        class="relative inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-300 lg:flex-initial"
                        :class="filtro === 'cancelado'
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-600 hover:bg-purple-50 hover:text-purple-800'"
                        @click="filtro = 'cancelado'"
                    >
                        <XCircle :size="14" />
                        Cancelados
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :class="filtro === 'cancelado' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                        >
                            {{ proyectosCanceladosLista.length }}
                        </span>
                    </button>
                </div>

                <!-- Chip de total segun filtro -->
                <span
                    class="rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="filtro === 'activo'
                        ? 'border-purple-200 bg-purple-50 text-purple-800'
                        : filtro === 'terminado'
                            ? 'border-neutral-200 bg-neutral-50 text-neutral-700'
                            : 'border-neutral-200 bg-neutral-50 text-neutral-600'"
                >
                    <template v-if="filtro === 'activo'">
                        Por cobrar: {{ formatCurrency(totalPorCobrar) }}
                    </template>
                    <template v-else-if="filtro === 'terminado'">
                        Total cobrado: {{ formatCurrency(totalCobradoTerminados) }}
                    </template>
                    <template v-else>
                        Total cobrado: {{ formatCurrency(totalCancelados) }}
                    </template>
                </span>
            </div>

            <!-- Empty state general (sin proyectos) -->
            <div
                v-if="proyectos.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <Briefcase :size="24" />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    Aun no tienes proyectos freelance registrados
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    Agrega el primero con el boton de arriba.
                </p>
            </div>

            <!-- Empty state del filtro -->
            <div
                v-else-if="proyectosFiltrados.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <component
                        :is="filtro === 'activo' ? Clock : filtro === 'terminado' ? CheckCircle2 : XCircle"
                        :size="24"
                    />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    <template v-if="filtro === 'activo'">No tienes proyectos activos</template>
                    <template v-else-if="filtro === 'terminado'">Aun no has terminado ningun proyecto</template>
                    <template v-else>No tienes proyectos cancelados</template>
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    <template v-if="filtro === 'activo'">Todos tus proyectos estan en otra categoria.</template>
                    <template v-else-if="filtro === 'terminado'">Cuando marques uno como terminado aparecera aqui.</template>
                    <template v-else>Los proyectos cancelados apareceran aqui.</template>
                </p>
            </div>

            <!-- Grid de cards -->
            <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="(proyecto, i) in proyectosFiltrados"
                    :key="proyecto.id"
                    class="card-lift group animate-fade-in relative overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm"
                    :style="{ animationDelay: `${i * 40}ms` }"
                >
                    <div
                        class="h-1.5 w-full"
                        :style="{ background: `linear-gradient(90deg, ${ESTADO_COLOR[proyecto.estado]}, ${ESTADO_COLOR[proyecto.estado]}77)` }"
                    ></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                                    :style="{
                                        backgroundColor: ESTADO_COLOR[proyecto.estado] + '1a',
                                        color: ESTADO_COLOR[proyecto.estado],
                                        boxShadow: `0 4px 12px ${ESTADO_COLOR[proyecto.estado]}33`,
                                    }"
                                >
                                    <Briefcase :size="18" stroke-width="2.2" />
                                    <span
                                        class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset"
                                        :style="{ borderColor: ESTADO_COLOR[proyecto.estado] }"
                                    ></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-neutral-900">{{ proyecto.nombre }}</p>
                                    <p v-if="proyecto.cliente" class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500">
                                        <User :size="11" /> {{ proyecto.cliente }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="inline-flex shrink-0 items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                :style="{
                                    backgroundColor: ESTADO_COLOR[proyecto.estado] + '14',
                                    color: ESTADO_COLOR[proyecto.estado],
                                    borderColor: ESTADO_COLOR[proyecto.estado] + '40',
                                }"
                            >
                                <component :is="ESTADO_ICON[proyecto.estado]" :size="11" />
                                {{ estados[proyecto.estado] }}
                            </span>
                        </div>

                        <p v-if="proyecto.descripcion" class="mt-3 line-clamp-2 text-xs text-neutral-500">
                            {{ proyecto.descripcion }}
                        </p>

                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                                Pendiente
                            </span>
                            <p
                                class="text-2xl font-bold tracking-tight tabular-nums"
                                :style="{ color: ESTADO_COLOR[proyecto.estado] }"
                            >
                                {{ formatCurrency(proyecto.pendiente) }}
                            </p>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <ProgressBar
                                :value="proyecto.progreso"
                                class="flex-1"
                                :color="ESTADO_COLOR[proyecto.estado]"
                            />
                            <span class="w-10 shrink-0 text-right text-xs font-bold text-neutral-700">
                                {{ proyecto.progreso }}%
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center justify-between gap-1 text-[11px] text-neutral-500">
                            <span class="inline-flex items-center gap-1">
                                <Coins :size="11" />
                                {{ formatCurrency(proyecto.pagado) }} de {{ formatCurrency(proyecto.monto_cobrado) }}
                            </span>
                            <span v-if="proyecto.fecha_entrega" class="inline-flex items-center gap-1">
                                <CalendarClock :size="11" /> {{ formatFecha(proyecto.fecha_entrega) }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center gap-2 border-t border-neutral-100 pt-4">
                            <Link
                                :href="route('proyectos.show', proyecto.id)"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 shine rounded-xl bg-neutral-950 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-purple-900/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-purple-800 hover:shadow-lg hover:shadow-purple-700/30 active:scale-95"
                            >
                                <Eye :size="13" /> Ver / pago
                            </Link>
                            <button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-800 transition-all duration-200 hover:border-purple-300 hover:bg-purple-100 hover:shadow-sm"
                                title="Editar"
                                @click="abrirEditar(proyecto)"
                            >
                                <Pencil :size="14" />
                            </button>
                            <button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 hover:shadow-sm"
                                title="Eliminar"
                                @click="eliminar(proyecto)"
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

                <div class="relative overflow-hidden border-b border-neutral-200 bg-white px-8 py-6 text-neutral-900">

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200">
                            <component :is="editando ? Pencil : Sparkles" :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">
                                {{ editando ? 'Editar proyecto' : 'Nuevo proyecto' }}
                            </h2>
                            <p class="text-xs text-neutral-500">
                                {{ editando ? 'Actualiza los datos del proyecto' : 'Registra un nuevo trabajo freelance' }}
                            </p>
                        </div>
                    </div>
                </div>

                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <div>
                            <InputLabel value="Nombre del proyecto" />
                            <TextInput
                                v-model="form.nombre"
                                class="mt-1.5"
                                placeholder="Ej. Pagina web para cafeteria X"
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Cliente (opcional)" />
                                <TextInput
                                    v-model="form.cliente"
                                    class="mt-1.5"
                                    placeholder="Para quien es?"
                                />
                                <InputError :message="form.errors.cliente" />
                            </div>
                            <div>
                                <InputLabel value="Cuanto vas a cobrar" />
                                <CurrencyInput v-model="form.monto_cobrado" class="mt-1.5" />
                                <InputError :message="form.errors.monto_cobrado" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Descripción (opcional)" />
                            <Textarea
                                v-model="form.descripcion"
                                class="mt-1.5"
                                :rows="3"
                                placeholder="De que trata el proyecto?"
                            />
                            <InputError :message="form.errors.descripcion" />
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Fecha de inicio" />
                                <TextInput v-model="form.fecha_inicio" type="date" class="mt-1.5" />
                                <InputError :message="form.errors.fecha_inicio" />
                            </div>
                            <div>
                                <InputLabel value="Fecha de entrega (opcional)" />
                                <TextInput v-model="form.fecha_entrega" type="date" class="mt-1.5" />
                                <InputError :message="form.errors.fecha_entrega" />
                            </div>
                        </div>

                        <div v-if="editando">
                            <InputLabel value="Estado del proyecto" />
                            <SelectInput v-model="form.estado" class="mt-1.5">
                                <option v-for="(label, value) in estados" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </SelectInput>
                            <InputError :message="form.errors.estado" />
                        </div>

                        <div>
                            <InputLabel value="Notas (opcional)" />
                            <Textarea
                                v-model="form.notas"
                                class="mt-1.5"
                                :rows="3"
                                placeholder="Ej. Acuerdos, alcance, condiciones de pago..."
                            />
                            <InputError :message="form.errors.notas" />
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
                        >
                            <component :is="editando ? Pencil : Check" :size="14" />
                            {{ editando ? 'Guardar cambios' : 'Crear proyecto' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>