<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency } from '@/utils';

const props = defineProps({
    cuentas: Array,
    tipos: Object,
});

const COLORES = ['#16a34a', '#0ea5e9', '#a855f7', '#f97316', '#eab308', '#ec4899', '#dc2626', '#64748b'];

const showModal = ref(false);
const editando = ref(null);
const confirmando = ref(null);

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

function eliminar() {
    router.delete(route('cuentas.destroy', confirmando.value.id), { onFinish: () => (confirmando.value = null) });
}
</script>

<template>
    <Head title="Cuentas" />

    <AuthenticatedLayout title="Cuentas" subtitle="Efectivo, bancos, tarjetas y ahorros">
        <div class="mb-5 flex justify-end">
            <PrimaryButton @click="abrirCrear">+ Nueva cuenta</PrimaryButton>
        </div>

        <div v-if="cuentas.length === 0" class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
            Todavía no tienes cuentas registradas. Crea la primera con el botón de arriba.
        </div>

        <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="cuenta in cuentas"
                :key="cuenta.id"
                class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm shadow-gray-200/50"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: cuenta.color }" />
                        <span class="font-bold text-ink-900">{{ cuenta.nombre }}</span>
                    </div>
                    <span v-if="!cuenta.activa" class="text-xs font-semibold text-gray-400">Inactiva</span>
                </div>
                <div class="mt-1 text-xs text-gray-400">{{ tipos[cuenta.tipo] }}</div>
                <div class="mt-3 text-2xl font-extrabold" :class="cuenta.saldo < 0 ? 'text-rose-600' : 'text-ink-900'">
                    {{ formatCurrency(cuenta.saldo) }}
                </div>
                <div class="mt-4 flex gap-2">
                    <button
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-ink-800 hover:bg-gray-50"
                        @click="abrirEditar(cuenta)"
                    >
                        Editar
                    </button>
                    <button
                        class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                        @click="confirmando = cuenta"
                    >
                        Eliminar
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
                            <TextInput v-model="form.saldo_inicial" type="number" step="0.01" class="mt-1" />
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
                                class="h-7 w-7 rounded-full ring-offset-2 transition"
                                :class="form.color === color ? 'ring-2 ring-ink-800' : ''"
                                :style="{ backgroundColor: color }"
                                @click="form.color = color"
                            />
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

        <ConfirmDialog
            :show="!!confirmando"
            title="Eliminar cuenta"
            :message="confirmando ? `¿Eliminar la cuenta '${confirmando.nombre}'? Si tiene movimientos, se marcará como inactiva en vez de borrarse.` : ''"
            @confirm="eliminar"
            @cancel="confirmando = null"
        />
    </AuthenticatedLayout>
</template>
