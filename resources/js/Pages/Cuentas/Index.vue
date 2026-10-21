<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency } from '@/utils';
import { confirmar } from '@/lib/alertas';
import {
    Plus, Pencil, Trash2, Wallet, Landmark, PiggyBank, CreditCard,
    TrendingUp, Layers, Check, Sparkles,
} from '@lucide/vue';

const props = defineProps({
    cuentas: Array,
    tipos: Object,
});

const COLORES = ['#9333ea', '#7c3aed', '#a855f7', '#6366f1', '#0ea5e9', '#16a34a', '#f97316', '#eab308', '#ec4899', '#dc2626', '#64748b'];
const ICONOS_TIPO = {
    efectivo: Wallet,
    banco: Landmark,
    ahorro: PiggyBank,
    tarjeta_credito: CreditCard,
    tarjeta_debito: CreditCard,
    inversion: TrendingUp,
    otro: Layers,
};

const showModal = ref(false);
const editando = ref(null);

const form = useForm({
    nombre: '',
    tipo: 'efectivo',
    saldo_inicial: 0,
    color: COLORES[0],
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.tipo = 'efectivo';
    form.saldo_inicial = 0;
    form.color = COLORES[0];
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(cuenta) {
    editando.value = cuenta;
    form.nombre = cuenta.nombre;
    form.tipo = cuenta.tipo;
    form.saldo_inicial = cuenta.saldo_inicial;
    form.color = cuenta.color;
    form.notas = cuenta.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('cuentas.update', editando.value.id), {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post(route('cuentas.store'), {
            onSuccess: () => (showModal.value = false),
        });
    }
}

function eliminar(cuenta) {
    confirmar({
        title: 'Eliminar cuenta',
        content: `Eliminar la cuenta "${cuenta.nombre}"? Si tiene movimientos, se marcara como inactiva en vez de borrarse.`,
        onOk: () => router.delete(route('cuentas.destroy', cuenta.id)),
    });
}
</script>

<template>
    <Head title="Cuentas" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px]">

            <!-- Header de seccion -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30 transition-transform duration-500 hover:rotate-12 hover:scale-110">
                        <Landmark :size="22" />
                    </span>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                            Cuentas
                        </h1>
                        <p class="text-sm text-neutral-500">
                            Efectivo, bancos, tarjetas y ahorros
                        </p>
                    </div>
                </div>

                <PrimaryButton
                    @click="abrirCrear"
                >
                    <Plus :size="16" /> Nueva cuenta
                </PrimaryButton>
            </div>

            <!-- Contadores -->
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-800">
                    {{ cuentas.length }} cuenta(s)
                </span>
                <span class="rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-xs font-semibold text-neutral-600">
                    Saldo total: {{ formatCurrency(cuentas.reduce((s, c) => s + Number(c.saldo || 0), 0)) }}
                </span>
            </div>

            <!-- Empty state -->
            <div
                v-if="cuentas.length === 0"
                class="rounded-2xl border-2 border-dashed border-purple-200 bg-purple-50/30 py-16 text-center"
            >
                <div class="mx-auto flex h-14 w-14 animate-bounce items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-neutral-950 text-white shadow-md shadow-purple-900/30">
                    <Wallet :size="24" />
                </div>
                <p class="mt-4 text-base font-semibold text-neutral-800">
                    Todavia no tienes cuentas registradas
                </p>
                <p class="mt-1 text-sm text-neutral-500">
                    Crea la primera con el boton de arriba.
                </p>
            </div>

            <!-- Grid de cuentas -->
            <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="(cuenta, i) in cuentas"
                    :key="cuenta.id"
                    class="card-lift group animate-fade-in relative overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm"
                    :style="{ animationDelay: `${i * 40}ms` }"
                >
                    <div
                        class="h-1.5 w-full"
                        :style="{ background: `linear-gradient(90deg, ${cuenta.color}, ${cuenta.color}77)` }"
                    ></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-xl shadow-sm transition-transform duration-300"
                                    :style="{
                                        backgroundColor: cuenta.color + '1a',
                                        color: cuenta.color,
                                        boxShadow: `0 4px 12px ${cuenta.color}33`,
                                    }"
                                >
                                    <component :is="ICONOS_TIPO[cuenta.tipo] || Layers" :size="18" stroke-width="2.2" />
                                    <span
                                        class="absolute inset-0 rounded-xl opacity-40 ring-2 ring-inset"
                                        :style="{ borderColor: cuenta.color }"
                                    ></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-neutral-900">{{ cuenta.nombre }}</p>
                                    <p class="text-[11px] font-medium uppercase tracking-wider text-neutral-400">
                                        {{ tipos[cuenta.tipo] }}
                                    </p>
                                </div>
                            </div>

                            <span
                                v-if="!cuenta.activa"
                                class="shrink-0 rounded-full border border-neutral-200 bg-neutral-50 px-2 py-0.5 text-[10px] font-semibold text-neutral-500"
                            >
                                Inactiva
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">
                                Saldo actual
                            </p>
                            <p
                                class="mt-0.5 text-2xl font-bold tracking-tight tabular-nums"
                                :class="cuenta.saldo < 0 ? 'text-rose-600' : 'text-neutral-900'"
                            >
                                {{ formatCurrency(cuenta.saldo) }}
                            </p>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-3 py-2 text-xs font-semibold text-purple-800 transition-all duration-200 hover:border-purple-300 hover:bg-purple-100 hover:shadow-sm"
                                @click="abrirEditar(cuenta)"
                            >
                                <Pencil :size="13" /> Editar
                            </button>
                            <button
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2 text-xs font-semibold text-neutral-700 transition-all duration-200 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 hover:shadow-sm"
                                @click="eliminar(cuenta)"
                            >
                                <Trash2 :size="13" /> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL ancho -->
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
                                {{ editando ? 'Editar cuenta' : 'Nueva cuenta' }}
                            </h2>
                            <p class="text-xs text-neutral-500">
                                {{ editando ? 'Actualiza los datos de la cuenta' : 'Registra una nueva cuenta en tu sistema' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form class="p-8" @submit.prevent="guardar">

                    <div class="space-y-6">

                        <!-- Nombre (ancho completo) -->
                        <div>
                            <InputLabel value="Nombre de la cuenta" />
                            <TextInput
                                v-model="form.nombre"
                                class="mt-1.5"
                                placeholder="Ej. BBVA debito, Efectivo, Nu, Cuenta nomina..."
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <!-- Tipo + Saldo en 2 columnas -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Tipo de cuenta" />
                                <SelectInput v-model="form.tipo" class="mt-1.5">
                                    <option v-for="(label, value) in tipos" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </SelectInput>
                                <InputError :message="form.errors.tipo" />
                            </div>
                            <div>
                                <InputLabel value="Saldo inicial" />
                                <CurrencyInput v-model="form.saldo_inicial" class="mt-1.5" />
                                <InputError :message="form.errors.saldo_inicial" />
                            </div>
                        </div>

                        <!-- Color (mas espacioso) -->
                        <div>
                            <InputLabel value="Color de la cuenta" />
                            <p class="mt-1 text-xs text-neutral-500">
                                Elige un color para identificar rapidamente esta cuenta.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-3">
                                <button
                                    v-for="color in COLORES"
                                    :key="color"
                                    type="button"
                                    class="group relative flex h-10 w-10 items-center justify-center rounded-full ring-offset-2 transition-all duration-200"
                                    :class="form.color === color ? 'ring-2 ring-neutral-900 ring-offset-2' : 'hover:ring-2 hover:ring-neutral-200'"
                                    :style="{
                                        backgroundColor: color,
                                        boxShadow: form.color === color ? `0 6px 18px ${color}66` : 'none',
                                    }"
                                    @click="form.color = color"
                                >
                                    <Check
                                        v-if="form.color === color"
                                        :size="18"
                                        class="text-white drop-shadow"
                                        stroke-width="3"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <InputLabel value="Notas (opcional)" />
                            <Textarea
                                v-model="form.notas"
                                class="mt-1.5"
                                :rows="3"
                                placeholder="Ej. Cuenta nomina sin comisiones, tarjeta para gastos fijos..."
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
                            {{ editando ? 'Guardar cambios' : 'Crear cuenta' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>