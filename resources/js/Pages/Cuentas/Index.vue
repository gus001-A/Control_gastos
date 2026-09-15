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
import { Plus, Pencil, Trash2, Wallet, Landmark, PiggyBank, CreditCard, TrendingUp, Layers, Check } from '@lucide/vue';

const props = defineProps({
    cuentas: Array,
    tipos: Object,
});

const COLORES = ['#9333ea', '#0ea5e9', '#16a34a', '#f97316', '#eab308', '#ec4899', '#dc2626', '#64748b'];
const ICONOS_TIPO = { efectivo: Wallet, banco: Landmark, ahorro: PiggyBank, tarjeta_credito: CreditCard, tarjeta_debito: CreditCard, inversion: TrendingUp, otro: Layers };

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
        form.put(route('cuentas.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('cuentas.store'), { onSuccess: () => (showModal.value = false) });
    }
}

function eliminar(cuenta) {
    confirmar({
        title: 'Eliminar cuenta',
        content: `¿Eliminar la cuenta "${cuenta.nombre}"? Si tiene movimientos, se marcará como inactiva en vez de borrarse.`,
        onOk: () => router.delete(route('cuentas.destroy', cuenta.id)),
    });
}
</script>

<template>
    <Head title="Cuentas" />

    <AuthenticatedLayout title="Cuentas" subtitle="Efectivo, bancos, tarjetas y ahorros">
        <div class="mb-5 flex justify-end">
            <PrimaryButton @click="abrirCrear"><Plus :size="16" /> Nueva cuenta</PrimaryButton>
        </div>

        <div v-if="cuentas.length === 0" class="rounded-2xl border border-dashed border-ink-300 bg-white py-16 text-center text-ink-400">
            Todavía no tienes cuentas registradas. Crea la primera con el botón de arriba.
        </div>

        <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="(cuenta, i) in cuentas"
                :key="cuenta.id"
                class="group animate-fade-in rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-glow"
                :style="{ animationDelay: `${i * 40}ms` }"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                            :style="{ backgroundColor: cuenta.color + '18', color: cuenta.color }"
                        >
                            <component :is="ICONOS_TIPO[cuenta.tipo] || Layers" :size="17" stroke-width="2.2" />
                        </div>
                        <span class="font-bold text-ink-900">{{ cuenta.nombre }}</span>
                    </div>
                    <span v-if="!cuenta.activa" class="text-xs font-semibold text-ink-400">Inactiva</span>
                </div>
                <div class="mt-2 text-xs text-ink-400">{{ tipos[cuenta.tipo] }}</div>
                <div class="mt-2 text-2xl font-extrabold tracking-tight" :class="cuenta.saldo < 0 ? 'text-rose-600' : 'text-ink-900'">
                    {{ formatCurrency(cuenta.saldo) }}
                </div>
                <div class="mt-4 flex gap-2">
                    <button
                        class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-800 transition hover:border-brand-200 hover:bg-brand-50"
                        @click="abrirEditar(cuenta)"
                    >
                        <Pencil :size="13" /> Editar
                    </button>
                    <button
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                        @click="eliminar(cuenta)"
                    >
                        <Trash2 :size="13" /> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">{{ editando ? 'Editar cuenta' : 'Nueva cuenta' }}</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Nombre" />
                        <TextInput v-model="form.nombre" class="mt-1" placeholder="Ej. BBVA débito, Efectivo, Nu..." />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Tipo" />
                            <SelectInput v-model="form.tipo" class="mt-1">
                                <option v-for="(label, value) in tipos" :key="value" :value="value">{{ label }}</option>
                            </SelectInput>
                        </div>
                        <div>
                            <InputLabel value="Saldo inicial" />
                            <CurrencyInput v-model="form.saldo_inicial" class="mt-1" />
                            <InputError :message="form.errors.saldo_inicial" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Color" />
                        <div class="mt-2 flex gap-2">
                            <button
                                v-for="color in COLORES"
                                :key="color"
                                type="button"
                                class="flex h-7 w-7 items-center justify-center rounded-full ring-offset-2 transition hover:scale-110"
                                :class="form.color === color ? 'ring-2 ring-ink-800' : ''"
                                :style="{ backgroundColor: color }"
                                @click="form.color = color"
                            >
                                <Check v-if="form.color === color" :size="14" class="text-white" />
                            </button>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Notas (opcional)" />
                        <Textarea v-model="form.notas" class="mt-1" :rows="2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
