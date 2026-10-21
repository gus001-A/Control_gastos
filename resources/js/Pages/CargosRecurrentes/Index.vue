<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Badge from '@/Components/Badge.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar, exito, error as mostrarError } from '@/lib/alertas';
import {
    Plus, Pencil, Trash2, CheckCircle2, Repeat, Home, Dumbbell, Tv,
    Wifi, Wallet, CalendarDays, Sparkles, Check,
} from '@lucide/vue';

const props = defineProps({
    cargos: Array,
    frecuencias: Object,
    cuentas: Array,
    categorias: Array,
});

const ICONOS_SUGERIDOS = { Renta: Home, Gimnasio: Dumbbell, Streaming: Tv, Internet: Wifi };

function iconoPara(nombre) {
    const clave = Object.keys(ICONOS_SUGERIDOS).find((k) => nombre?.toLowerCase().includes(k.toLowerCase()));
    return clave ? ICONOS_SUGERIDOS[clave] : Repeat;
}

function estadoFecha(cargo) {
    if (!cargo.activo) return { texto: 'Pausado', color: '#94a3b8' };
    if (cargo.vencido) return { texto: `Vencio hace ${Math.abs(cargo.dias_para_vencer)} dia(s)`, color: '#dc2626' };
    if (cargo.dias_para_vencer === 0) return { texto: 'Vence hoy', color: '#dc2626' };
    if (cargo.dias_para_vencer <= 3) return { texto: `En ${cargo.dias_para_vencer} dia(s)`, color: '#d97706' };
    return { texto: `En ${cargo.dias_para_vencer} dia(s)`, color: '#0ea5e9' };
}

const totalMensualEstimado = computed(() => {
    const factor = { semanal: 4.33, quincenal: 2, mensual: 1, bimestral: 0.5, trimestral: 1 / 3, semestral: 1 / 6, anual: 1 / 12 };
    return props.cargos.filter((c) => c.activo).reduce((acc, c) => acc + Number(c.monto) * (factor[c.frecuencia] || 1), 0);
});

const cargosActivos = computed(() => props.cargos.filter((c) => c.activo).length);

const showModal = ref(false);
const editando = ref(null);
const form = useForm({
    nombre: '',
    cuenta_id: '',
    categoria_id: '',
    monto: '',
    frecuencia: 'mensual',
    proxima_fecha: hoyIso(),
    activo: true,
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.cuenta_id = props.cuentas[0]?.id || '';
    form.categoria_id = '';
    form.monto = '';
    form.frecuencia = 'mensual';
    form.proxima_fecha = hoyIso();
    form.activo = true;
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(cargo) {
    editando.value = cargo;
    form.nombre = cargo.nombre;
    form.cuenta_id = cargo.cuenta_id;
    form.categoria_id = cargo.categoria_id || '';
    form.monto = cargo.monto;
    form.frecuencia = cargo.frecuencia;
    form.proxima_fecha = cargo.proxima_fecha.slice(0, 10);
    form.activo = cargo.activo;
    form.notas = cargo.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('recurrentes.update', editando.value.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('recurrentes.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function eliminar(cargo) {
    confirmar({
        title: 'Eliminar cargo recurrente',
        content: `Eliminar "${cargo.nombre}"? No se borraran los movimientos ya registrados.`,
        onOk: () => router.delete(route('recurrentes.destroy', cargo.id)),
    });
}

function pagarAhora(cargo) {
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
</script>

<template>
    <Head title="Cargos recurrentes" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <Repeat :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Cargos recurrentes
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Renta, streaming, gimnasio y todo lo que pagas seguido
                        </p>
                    </div>
                </div>

                <PrimaryButton
                    @click="abrirCrear"
                >
                    <Plus :size="16" /> Nuevo cargo
                </PrimaryButton>
            </div>

            <!-- Contadores -->
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-800">
                    {{ cargos.length }} cargo(s)
                </span>
                <span class="rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-xs font-semibold text-neutral-600">
                    {{ cargosActivos }} activo(s)
                </span>
                <span class="rounded-full border border-purple-200 bg-white px-3 py-1 text-xs font-semibold text-purple-800">
                    Estimado mensual: {{ formatCurrency(totalMensualEstimado) }}
                </span>
            </div>

            <!-- Empty state -->
            <div
                v-if="cargos.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <Repeat :size="24" />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    Aun no tienes cargos recurrentes
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    Agrega tu renta, streaming, gimnasio, etc.
                </p>
            </div>

            <!-- Grid de cards -->
            <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="(cargo, i) in cargos"
                    :key="cargo.id"
                    class="card-lift group animate-fade-in relative overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm"
                    :class="{ 'opacity-60': !cargo.activo }"
                    :style="{ animationDelay: `${i * 40}ms` }"
                >
                    <!-- Barra superior segun estado -->
                    <div
                        class="h-1.5 w-full"
                        :style="{ background: `linear-gradient(90deg, ${estadoFecha(cargo).color}, ${estadoFecha(cargo).color}77)` }"
                    ></div>

                    <div class="p-5">
                        <!-- Icono + nombre + badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-purple-700 shadow-sm transition-transform duration-300">
                                    <component :is="iconoPara(cargo.nombre)" :size="18" stroke-width="2.2" />
                                    <span class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset ring-purple-200"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-neutral-900">{{ cargo.nombre }}</p>
                                    <p class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500">
                                        <Wallet :size="11" /> {{ cargo.cuenta.nombre }}
                                    </p>
                                </div>
                            </div>
                            <Badge v-if="cargo.categoria" :color="cargo.categoria.color">
                                {{ cargo.categoria.nombre }}
                            </Badge>
                        </div>

                        <!-- Monto + frecuencia -->
                        <div class="mt-4 flex items-end justify-between">
                            <p class="text-2xl font-bold tracking-tight tabular-nums text-neutral-900">
                                {{ formatCurrency(cargo.monto) }}
                            </p>
                            <span class="rounded-md border border-purple-200 bg-purple-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-purple-800">
                                {{ frecuencias[cargo.frecuencia] }}
                            </span>
                        </div>

                        <!-- Fecha + estado -->
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="inline-flex items-center gap-1 text-neutral-500">
                                <CalendarDays :size="11" />
                                {{ formatFecha(cargo.proxima_fecha) }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-bold"
                                :style="{
                                    color: estadoFecha(cargo).color,
                                    backgroundColor: estadoFecha(cargo).color + '15',
                                }"
                            >
                                {{ estadoFecha(cargo).texto }}
                            </span>
                        </div>

                        <!-- Acciones -->
                        <div class="mt-4 flex items-center gap-2 border-t border-neutral-100 pt-4">
                            <button
                                class="inline-flex flex-1 items-center justify-center gap-1.5 shine rounded-xl bg-neutral-950 px-3 py-2 text-xs font-semibold text-white shadow-md shadow-purple-900/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-purple-800 hover:shadow-lg hover:shadow-purple-700/30 active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                                :disabled="!cargo.activo"
                                @click="pagarAhora(cargo)"
                            >
                                <CheckCircle2 :size="13" /> Pagar ahora
                            </button>
                            <button
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-800 transition-all duration-200 hover:border-purple-300 hover:bg-purple-100 hover:shadow-sm"
                                title="Editar"
                                @click="abrirEditar(cargo)"
                            >
                                <Pencil :size="14" />
                            </button>
                            <button
                                class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-700 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 hover:shadow-sm"
                                title="Eliminar"
                                @click="eliminar(cargo)"
                            >
                                <Trash2 :size="14" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2xl (intermedio) -->
        <Modal :show="showModal" max-width="2xl" @close="showModal = false">
            <div class="overflow-hidden rounded-xl bg-white">

                <!-- Header del modal -->
                <div class="relative overflow-hidden border-b border-neutral-200 bg-white px-8 py-6 text-neutral-900">

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200">
                            <component :is="editando ? Pencil : Sparkles" :size="20" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight">
                                {{ editando ? 'Editar cargo recurrente' : 'Nuevo cargo recurrente' }}
                            </h2>
                            <p class="text-xs text-neutral-500">
                                {{ editando ? 'Actualiza los datos del cargo' : 'Registra un nuevo cobro periodico' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <!-- Nombre -->
                        <div>
                            <InputLabel value="Nombre del cargo" />
                            <TextInput
                                v-model="form.nombre"
                                class="mt-1.5"
                                placeholder="Ej. Renta, Netflix, Gimnasio, Internet..."
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <!-- Cuenta + Categoria -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Cuenta con la que pagas" />
                                <SelectInput v-model="form.cuenta_id" class="mt-1.5">
                                    <option v-for="c in cuentas" :key="c.id" :value="c.id">
                                        {{ c.nombre }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.cuenta_id" />
                            </div>
                            <div>
                                <InputLabel value="Categoria (opcional)" />
                                <SelectInput v-model="form.categoria_id" class="mt-1.5">
                                    <option value="">(Sin categoria)</option>
                                    <option v-for="c in categorias" :key="c.id" :value="c.id">
                                        {{ c.nombre }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.categoria_id" />
                            </div>
                        </div>

                        <!-- Monto + Frecuencia -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Monto" />
                                <CurrencyInput v-model="form.monto" class="mt-1.5" />
                                <InputError :message="form.errors.monto" />
                            </div>
                            <div>
                                <InputLabel value="Frecuencia de pago" />
                                <SelectInput v-model="form.frecuencia" class="mt-1.5">
                                    <option v-for="(label, value) in frecuencias" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.frecuencia" />
                            </div>
                        </div>

                        <!-- Proximo cobro -->
                        <div>
                            <InputLabel value="Proximo cobro" />
                            <TextInput
                                v-model="form.proxima_fecha"
                                type="date"
                                class="mt-1.5 sm:max-w-xs"
                            />
                            <InputError :message="form.errors.proxima_fecha" />
                        </div>

                        <!-- Activo (solo al editar) -->
                        <div v-if="editando" class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                            <label class="flex items-center gap-2.5 text-sm font-medium text-neutral-800">
                                <input
                                    type="checkbox"
                                    v-model="form.activo"
                                    class="rounded border-neutral-300 text-purple-700 focus:ring-purple-500"
                                />
                                Cargo activo
                                <span class="text-xs font-normal text-neutral-500">
                                    (se muestra en la lista y se puede pagar)
                                </span>
                            </label>
                        </div>

                        <!-- Notas -->
                        <div>
                            <InputLabel value="Notas (opcional)" />
                            <Textarea
                                v-model="form.notas"
                                class="mt-1.5"
                                :rows="3"
                                placeholder="Ej. Se paga el dia 15 de cada mes, incluye IVA..."
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
                            {{ editando ? 'Guardar cambios' : 'Crear cargo' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>